<?php

/**
 * Withdrawal ("Odstúpenie od zmluvy") form on the "Vrátenie tovaru" page.
 *
 * Posts to admin-post.php, mails the shop and sends the customer a copy as the
 * confirmation of receipt, then redirects back with a status flag. Spam
 * protection (nonce, honeypot, timing token, per-IP rate limit) is shared with
 * the contact form in inc/contact-form.php.
 */

const GRACEART_RETURNS_ACTION = 'graceart_returns';
const GRACEART_RETURNS_NONCE = 'graceart_returns_nonce';

function graceartReturnsRedirect(string $url, string $status, array $extra = []): void
{
    $args = array_merge(['returns' => $status], $extra);

    wp_safe_redirect(add_query_arg($args, $url) . '#odstupenie-formular');
    exit;
}

add_action('admin_post_nopriv_' . GRACEART_RETURNS_ACTION, 'graceartHandleReturnsForm');
add_action('admin_post_' . GRACEART_RETURNS_ACTION, 'graceartHandleReturnsForm');

function graceartHandleReturnsForm(): void
{
    $redirect_to = isset($_POST['redirect_to']) ? wp_unslash($_POST['redirect_to']) : '';
    $redirect_to = wp_validate_redirect(esc_url_raw((string) $redirect_to), home_url('/'));

    if (! isset($_POST[GRACEART_RETURNS_NONCE]) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[GRACEART_RETURNS_NONCE])), GRACEART_RETURNS_ACTION)) {
        graceartReturnsRedirect($redirect_to, 'error', ['reason' => 'nonce']);
    }

    // Bots fill hidden fields; humans leave them alone.
    if (! empty($_POST['graceart_website'])) {
        graceartReturnsRedirect($redirect_to, 'sent');
    }

    if (! graceartContactTimingOk(sanitize_text_field(wp_unslash($_POST['graceart_ts'] ?? '')))) {
        graceartReturnsRedirect($redirect_to, 'sent');
    }

    $name = sanitize_text_field(wp_unslash($_POST['graceart_name'] ?? ''));
    $email = sanitize_email(wp_unslash($_POST['graceart_email'] ?? ''));
    $order = sanitize_text_field(wp_unslash($_POST['graceart_order'] ?? ''));
    $items = sanitize_textarea_field(wp_unslash($_POST['graceart_items'] ?? ''));
    $received = sanitize_text_field(wp_unslash($_POST['graceart_received'] ?? ''));
    $reason = sanitize_textarea_field(wp_unslash($_POST['graceart_reason'] ?? ''));
    $iban = strtoupper(preg_replace('~\s+~', '', sanitize_text_field(wp_unslash($_POST['graceart_iban'] ?? ''))));

    if ($name === '' || $order === '' || $items === '' || ! is_email($email)) {
        graceartReturnsRedirect($redirect_to, 'error', ['reason' => 'fields']);
    }

    if ($iban !== '' && ! preg_match('~^[A-Z]{2}\d{2}[A-Z0-9]{10,30}$~', $iban)) {
        graceartReturnsRedirect($redirect_to, 'error', ['reason' => 'iban']);
    }

    if (preg_match_all('~https?://|www\.~i', $name . ' ' . $items . ' ' . $reason) > 2) {
        graceartReturnsRedirect($redirect_to, 'sent');
    }

    if (graceartContactRateLimited()) {
        graceartReturnsRedirect($redirect_to, 'error', ['reason' => 'rate']);
    }

    $recipient = graceartContactEmail();

    if ($recipient === '') {
        graceartReturnsRedirect($redirect_to, 'error', ['reason' => 'recipient']);
    }

    $sent_at = wp_date('j. n. Y H:i');

    $body = sprintf(
        "%s\n\n%s: %s\n%s: %s\n%s: %s\n%s: %s\n%s: %s\n%s: %s\n\n%s:\n%s\n\n%s:\n%s\n",
        __('Spotrebiteľ oznamuje, že odstupuje od zmluvy o kúpe tovaru.', 'graceart'),
        __('Meno a priezvisko', 'graceart'),
        $name,
        __('E-mail', 'graceart'),
        $email,
        __('Číslo objednávky', 'graceart'),
        $order,
        __('Dátum prevzatia tovaru', 'graceart'),
        $received !== '' ? $received : '-',
        __('IBAN pre vrátenie platby', 'graceart'),
        $iban !== '' ? $iban : '-',
        __('Odoslané', 'graceart'),
        $sent_at,
        __('Tovar', 'graceart'),
        $items,
        __('Dôvod (nepovinné)', 'graceart'),
        $reason !== '' ? $reason : '-'
    );

    $sent = wp_mail(
        $recipient,
        sprintf(
            /* translators: %s: order number */
            __('Odstúpenie od zmluvy – objednávka %s', 'graceart'),
            $order
        ),
        $body,
        [
            'Content-Type: text/plain; charset=UTF-8',
            sprintf('Reply-To: %s <%s>', $name, $email),
        ]
    );

    if (! $sent) {
        graceartReturnsRedirect($redirect_to, 'error', ['reason' => 'mail']);
    }

    // Confirmation of receipt for the customer, on a durable medium (e-mail).
    wp_mail(
        $email,
        sprintf(
            /* translators: %s: shop name */
            __('Potvrdenie prijatia odstúpenia od zmluvy – %s', 'graceart'),
            wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES)
        ),
        __('Dobrý deň,', 'graceart') . "\n\n"
            . __('potvrdzujeme, že sme prijali vaše odstúpenie od zmluvy. Tovar nám prosím pošlite späť do 14 dní od odstúpenia, spolu s číslom objednávky. Peniaze vám vrátime do 14 dní od doručenia odstúpenia, najskôr však po prijatí tovaru alebo po preukázaní jeho odoslania.', 'graceart')
            . "\n\n" . __('Kópia vášho oznámenia:', 'graceart') . "\n\n" . $body,
        ['Content-Type: text/plain; charset=UTF-8']
    );

    graceartReturnsRedirect($redirect_to, 'sent');
}

/**
 * Feedback message for the form, based on the redirect flag.
 */
function graceartReturnsNotice(): string
{
    $status = isset($_GET['returns']) ? sanitize_key(wp_unslash($_GET['returns'])) : '';

    if ($status === 'sent') {
        return '<p class="graceart-contact-notice is-success">'
            . esc_html__('Ďakujeme, odstúpenie od zmluvy sme prijali. Potvrdenie sme vám poslali e-mailom.', 'graceart')
            . '</p>';
    }

    if ($status !== 'error') {
        return '';
    }

    $reason = isset($_GET['reason']) ? sanitize_key(wp_unslash($_GET['reason'])) : '';

    switch ($reason) {
        case 'fields':
            $text = __('Vyplňte prosím meno, platný e-mail, číslo objednávky a vrátený tovar.', 'graceart');
            break;
        case 'iban':
            $text = __('IBAN nemá správny formát. Skontrolujte ho alebo pole nechajte prázdne.', 'graceart');
            break;
        case 'rate':
            $text = __('Odoslali ste príliš veľa formulárov. Skúste to prosím neskôr.', 'graceart');
            break;
        case 'recipient':
            $text = __('Kontaktný e-mail nie je nastavený. Nastavte ho v WooCommerce → Nastavenia → Všeobecné.', 'graceart');
            break;
        default:
            $text = __('Formulár sa nepodarilo odoslať. Skúste to prosím znova alebo nám napíšte e-mail.', 'graceart');
            break;
    }

    return '<p class="graceart-contact-notice is-error">' . esc_html($text) . '</p>';
}

/**
 * URL of the page that uses the "Vrátenie tovaru" template, falling back to
 * the WooCommerce refund/returns page and then the home page.
 */
function graceartReturnsUrl(): string
{
    $pages = get_pages([
        'meta_key' => '_wp_page_template',
        'meta_value' => 'templates/returns.php',
        'number' => 1,
    ]);

    if ($pages) {
        return (string) get_permalink($pages[0]);
    }

    $page_id = (int) get_option('woocommerce_refund_returns_page_id');

    return $page_id > 0 ? (string) get_permalink($page_id) : home_url('/');
}

/**
 * Pointer to the withdrawal information on the order-received page and in the
 * customer's order e-mails, so the right of withdrawal is communicated again
 * with the order confirmation.
 */
function graceartReturnsNoticeText(): string
{
    return sprintf(
        /* translators: %s: link to the returns page */
        __('Od zmluvy môžete odstúpiť do 14 dní od prevzatia tovaru bez udania dôvodu. Postup a formulár nájdete na stránke %s.', 'graceart'),
        '<a href="' . esc_url(graceartReturnsUrl()) . '">' . esc_html__('Vrátenie tovaru', 'graceart') . '</a>'
    );
}

add_action('woocommerce_thankyou', function (): void {
    echo '<p class="graceart-returns-note">' . wp_kses_post(graceartReturnsNoticeText()) . '</p>';
}, 30);

add_action('woocommerce_email_after_order_table', function ($order, $sent_to_admin, $plain_text): void {
    if ($sent_to_admin) {
        return;
    }

    if ($plain_text) {
        echo "\n" . wp_strip_all_tags(graceartReturnsNoticeText()) . ' ' . esc_url(graceartReturnsUrl()) . "\n";

        return;
    }

    echo '<p>' . wp_kses_post(graceartReturnsNoticeText()) . '</p>';
}, 20, 3);
