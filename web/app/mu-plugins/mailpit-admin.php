<?php

/**
 * Plugin Name: Mailpit viewer
 * Description: Reads the captured mail inbox over loopback and renders it in the admin. Non-production only.
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Mailpit listens on loopback only, so the API is never reachable from outside.
 */
function graceartMailpitBase(): string
{
    return apply_filters('graceart_mailpit_base', 'http://127.0.0.1:8025');
}

function graceartMailpitAvailable(): bool
{
    return defined('WP_ENV') && WP_ENV !== 'production';
}

/**
 * @return array<string, mixed>|null
 */
function graceartMailpitGet(string $path): ?array
{
    $response = wp_remote_get(graceartMailpitBase() . $path, ['timeout' => 5]);

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return null;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    return is_array($data) ? $data : null;
}

function graceartMailpitMenu(): void
{
    if (! graceartMailpitAvailable()) {
        return;
    }

    add_management_page(
        __('Mailpit', 'graceart'),
        __('Mailpit', 'graceart'),
        'manage_options',
        'graceart-mailpit',
        'graceartMailpitPage',
    );
}
add_action('admin_menu', 'graceartMailpitMenu');

function graceartMailpitUrl(array $args = []): string
{
    return add_query_arg(
        array_merge(['page' => 'graceart-mailpit'], $args),
        admin_url('tools.php'),
    );
}

/**
 * @param  array<string, mixed>|null  $address
 */
function graceartMailpitAddress(?array $address): string
{
    if (! $address) {
        return '—';
    }

    $name = $address['Name'] ?? '';
    $mail = $address['Address'] ?? '';

    return $name ? sprintf('%s <%s>', $name, $mail) : $mail;
}

/**
 * @param  array<int, array<string, mixed>>|null  $addresses
 */
function graceartMailpitAddresses(?array $addresses): string
{
    if (! $addresses) {
        return '—';
    }

    return implode(', ', array_map('graceartMailpitAddress', $addresses));
}

function graceartMailpitPage(): void
{
    $id = isset($_GET['message']) ? sanitize_text_field(wp_unslash($_GET['message'])) : '';

    echo '<div class="wrap">';

    if ($id !== '') {
        graceartMailpitDetail($id);
    } else {
        graceartMailpitList();
    }

    echo '</div>';
}

function graceartMailpitList(): void
{
    $data = graceartMailpitGet('/api/v1/messages?limit=200');

    printf('<h1 class="wp-heading-inline">%s</h1>', esc_html__('Mailpit', 'graceart'));

    if ($data === null) {
        printf(
            '<div class="notice notice-error"><p>%s</p></div>',
            esc_html__('Mailpit nebeží alebo neodpovedá na 127.0.0.1:8025.', 'graceart'),
        );

        return;
    }

    $messages = $data['messages'] ?? [];

    if ($messages) {
        printf(
            '<a href="%s" class="page-title-action" onclick="return confirm(\'%s\')">%s</a>',
            esc_url(wp_nonce_url(
                admin_url('admin-post.php?action=graceart_mailpit_delete_all'),
                'graceart_mailpit_delete_all',
            )),
            esc_js(__('Zmazať všetky zachytené e-maily?', 'graceart')),
            esc_html__('Vymazať schránku', 'graceart'),
        );
    }

    printf(
        '<p class="description">%s</p>',
        esc_html__('E-maily z tohto prostredia sa nedoručujú príjemcom, iba sa zachytávajú.', 'graceart'),
    );

    if (! $messages) {
        printf('<p>%s</p>', esc_html__('Schránka je prázdna.', 'graceart'));

        return;
    }

    echo '<table class="widefat striped"><thead><tr>';
    printf('<th>%s</th>', esc_html__('Dátum', 'graceart'));
    printf('<th>%s</th>', esc_html__('Od', 'graceart'));
    printf('<th>%s</th>', esc_html__('Komu', 'graceart'));
    printf('<th>%s</th>', esc_html__('Predmet', 'graceart'));
    printf('<th>%s</th>', esc_html__('Prílohy', 'graceart'));
    echo '</tr></thead><tbody>';

    foreach ($messages as $message) {
        $created = isset($message['Created'])
            ? mysql2date('j.n.Y H:i', get_date_from_gmt($message['Created']))
            : '—';

        echo '<tr>';
        printf('<td>%s</td>', esc_html($created));
        printf('<td>%s</td>', esc_html(graceartMailpitAddress($message['From'] ?? null)));
        printf('<td>%s</td>', esc_html(graceartMailpitAddresses($message['To'] ?? null)));
        printf(
            '<td><strong><a href="%s">%s</a></strong></td>',
            esc_url(graceartMailpitUrl(['message' => $message['ID'] ?? ''])),
            esc_html($message['Subject'] ?? '(bez predmetu)'),
        );
        printf('<td>%s</td>', esc_html((string) ($message['Attachments'] ?? 0)));
        echo '</tr>';
    }

    echo '</tbody></table>';
}

function graceartMailpitDetail(string $id): void
{
    $message = graceartMailpitGet('/api/v1/message/' . rawurlencode($id));

    printf(
        '<h1 class="wp-heading-inline">%s</h1> <a href="%s" class="page-title-action">%s</a>',
        esc_html__('Mailpit', 'graceart'),
        esc_url(graceartMailpitUrl()),
        esc_html__('Späť na schránku', 'graceart'),
    );

    if ($message === null) {
        printf(
            '<div class="notice notice-error"><p>%s</p></div>',
            esc_html__('E-mail sa nepodarilo načítať.', 'graceart'),
        );

        return;
    }

    echo '<table class="widefat" style="margin:16px 0;max-width:900px;"><tbody>';
    printf(
        '<tr><th style="width:120px;">%s</th><td>%s</td></tr>',
        esc_html__('Predmet', 'graceart'),
        esc_html($message['Subject'] ?? ''),
    );
    printf(
        '<tr><th>%s</th><td>%s</td></tr>',
        esc_html__('Od', 'graceart'),
        esc_html(graceartMailpitAddress($message['From'] ?? null)),
    );
    printf(
        '<tr><th>%s</th><td>%s</td></tr>',
        esc_html__('Komu', 'graceart'),
        esc_html(graceartMailpitAddresses($message['To'] ?? null)),
    );
    printf(
        '<tr><th>%s</th><td>%s</td></tr>',
        esc_html__('Dátum', 'graceart'),
        esc_html($message['Date'] ?? ''),
    );
    echo '</tbody></table>';

    $attachments = $message['Attachments'] ?? [];

    if ($attachments) {
        printf('<h2>%s</h2><ul class="ul-disc">', esc_html__('Prílohy', 'graceart'));

        foreach ($attachments as $attachment) {
            printf(
                '<li><a href="%s">%s</a> <span class="description">(%s)</span></li>',
                esc_url(wp_nonce_url(
                    admin_url(sprintf(
                        'admin-post.php?action=graceart_mailpit_attachment&message=%s&part=%s',
                        rawurlencode($id),
                        rawurlencode((string) ($attachment['PartID'] ?? '')),
                    )),
                    'graceart_mailpit_attachment',
                )),
                esc_html($attachment['FileName'] ?? 'attachment'),
                esc_html(size_format((int) ($attachment['Size'] ?? 0))),
            );
        }

        echo '</ul>';
    }

    $html = $message['HTML'] ?? '';
    $text = $message['Text'] ?? '';

    if ($html !== '') {
        printf('<h2>%s</h2>', esc_html__('Náhľad', 'graceart'));
        printf(
            '<iframe sandbox="" srcdoc="%s" style="width:100%%;height:60vh;min-height:420px;border:1px solid #c3c4c7;background:#fff;"></iframe>',
            esc_attr($html),
        );
    }

    if ($text !== '') {
        printf('<h2>%s</h2>', esc_html__('Text', 'graceart'));
        printf(
            '<pre style="white-space:pre-wrap;background:#fff;border:1px solid #c3c4c7;padding:12px;">%s</pre>',
            esc_html($text),
        );
    }
}

function graceartMailpitAttachment(): void
{
    if (! current_user_can('manage_options') || ! graceartMailpitAvailable()) {
        wp_die(esc_html__('Nedostatočné oprávnenia.', 'graceart'));
    }

    check_admin_referer('graceart_mailpit_attachment');

    $id = isset($_GET['message']) ? sanitize_text_field(wp_unslash($_GET['message'])) : '';
    $part = isset($_GET['part']) ? sanitize_text_field(wp_unslash($_GET['part'])) : '';

    $response = wp_remote_get(
        sprintf('%s/api/v1/message/%s/part/%s', graceartMailpitBase(), rawurlencode($id), rawurlencode($part)),
        ['timeout' => 10],
    );

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        wp_die(esc_html__('Prílohu sa nepodarilo načítať.', 'graceart'));
    }

    $type = wp_remote_retrieve_header($response, 'content-type') ?: 'application/octet-stream';

    header('Content-Type: ' . $type);
    header('Content-Disposition: attachment; filename="' . $part . '"');
    echo wp_remote_retrieve_body($response); // phpcs:ignore WordPress.Security.EscapeOutput
    exit;
}
add_action('admin_post_graceart_mailpit_attachment', 'graceartMailpitAttachment');

function graceartMailpitDeleteAll(): void
{
    if (! current_user_can('manage_options') || ! graceartMailpitAvailable()) {
        wp_die(esc_html__('Nedostatočné oprávnenia.', 'graceart'));
    }

    check_admin_referer('graceart_mailpit_delete_all');

    wp_remote_request(graceartMailpitBase() . '/api/v1/messages', [
        'method' => 'DELETE',
        'timeout' => 10,
    ]);

    wp_safe_redirect(graceartMailpitUrl());
    exit;
}
add_action('admin_post_graceart_mailpit_delete_all', 'graceartMailpitDeleteAll');
