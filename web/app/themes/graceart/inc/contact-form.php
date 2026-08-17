<?php

/**
 * Contact form on the "Kontakt" page.
 *
 * Posts to admin-post.php, mails graceartContactEmail() via wp_mail() and
 * redirects back to the page with a status flag.
 */

const GRACEART_CONTACT_ACTION = 'graceart_contact';
const GRACEART_CONTACT_NONCE = 'graceart_contact_nonce';

function graceartContactRedirect(string $url, string $status, array $extra = []): void
{
    $args = array_merge(['contact' => $status], $extra);

    wp_safe_redirect(add_query_arg($args, $url) . '#kontakt-formular');
    exit;
}

add_action('admin_post_nopriv_' . GRACEART_CONTACT_ACTION, 'graceartHandleContactForm');
add_action('admin_post_' . GRACEART_CONTACT_ACTION, 'graceartHandleContactForm');

function graceartHandleContactForm(): void
{
    $redirect_to = isset($_POST['redirect_to']) ? wp_unslash($_POST['redirect_to']) : '';
    $redirect_to = wp_validate_redirect(esc_url_raw((string) $redirect_to), home_url('/'));

    if (! isset($_POST[GRACEART_CONTACT_NONCE]) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[GRACEART_CONTACT_NONCE])), GRACEART_CONTACT_ACTION)) {
        graceartContactRedirect($redirect_to, 'error', ['reason' => 'nonce']);
    }

    // Bots fill hidden fields; humans leave them alone.
    if (! empty($_POST['graceart_website'])) {
        graceartContactRedirect($redirect_to, 'sent');
    }

    $name = sanitize_text_field(wp_unslash($_POST['graceart_name'] ?? ''));
    $email = sanitize_email(wp_unslash($_POST['graceart_email'] ?? ''));
    $message = sanitize_textarea_field(wp_unslash($_POST['graceart_message'] ?? ''));

    if ($name === '' || $message === '' || ! is_email($email)) {
        graceartContactRedirect($redirect_to, 'error', ['reason' => 'fields']);
    }

    $recipient = graceartContactEmail();

    if ($recipient === '') {
        graceartContactRedirect($redirect_to, 'error', ['reason' => 'recipient']);
    }

    $subject = sprintf(
        /* translators: %s: sender name */
        __('Správa z kontaktného formulára od %s', 'graceart'),
        $name
    );

    $body = sprintf(
        "%s: %s\n%s: %s\n\n%s\n",
        __('Meno', 'graceart'),
        $name,
        __('E-mail', 'graceart'),
        $email,
        $message
    );

    $sent = wp_mail(
        $recipient,
        $subject,
        $body,
        [
            'Content-Type: text/plain; charset=UTF-8',
            sprintf('Reply-To: %s <%s>', $name, $email),
        ]
    );

    graceartContactRedirect($redirect_to, $sent ? 'sent' : 'error', $sent ? [] : ['reason' => 'mail']);
}

/**
 * Feedback message for the form, based on the redirect flag.
 */
function graceartContactNotice(): string
{
    $status = isset($_GET['contact']) ? sanitize_key(wp_unslash($_GET['contact'])) : '';

    if ($status === 'sent') {
        return '<p class="graceart-contact-notice is-success">'
            . esc_html__('Ďakujeme, správa bola odoslaná. Odpovieme čo najskôr.', 'graceart')
            . '</p>';
    }

    if ($status !== 'error') {
        return '';
    }

    $reason = isset($_GET['reason']) ? sanitize_key(wp_unslash($_GET['reason'])) : '';

    switch ($reason) {
        case 'fields':
            $text = __('Vyplňte prosím meno, platný e-mail a správu.', 'graceart');
            break;
        case 'recipient':
            $text = __('Kontaktný e-mail nie je nastavený. Nastavte ho v WooCommerce → Nastavenia → Všeobecné.', 'graceart');
            break;
        default:
            $text = __('Správu sa nepodarilo odoslať. Skúste to prosím znova.', 'graceart');
            break;
    }

    return '<p class="graceart-contact-notice is-error">' . esc_html($text) . '</p>';
}
