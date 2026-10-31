<?php

/**
 * Terms and conditions (PDF) attached to the order confirmation e-mails.
 *
 * The PDF is made from the "Obchodné podmienky" page (the one chosen as the
 * terms page in WooCommerce → Settings → Advanced → Page setup) with the PDF
 * maker of PDF Invoices & Packing Slips, so it always matches the page. It is
 * made once per version of the page and kept in the uploads folder.
 *
 * A PDF of your own can still be used: its address (a file from the media
 * library) goes into WooCommerce → Settings → General, and wins over the page.
 *
 * It goes to the e-mails a customer gets right after ordering: processing
 * (paid, card) and on hold (bank transfer).
 */

const GRACEART_TERMS_PDF_OPTION = 'graceart_terms_pdf_url';

add_filter('woocommerce_general_settings', function (array $settings): array {
    $field = [
        'title' => __('Obchodné podmienky (vlastné PDF)', 'graceart'),
        'desc' => __('Nepovinné. Prázdne = k e-mailu s potvrdením objednávky sa priloží PDF vytvorené zo stránky Obchodné podmienky. Ak chcete vlastný súbor, vložte adresu PDF z Médií („URL súboru").', 'graceart'),
        'id' => GRACEART_TERMS_PDF_OPTION,
        'type' => 'text',
        'desc_tip' => true,
    ];

    foreach ($settings as $index => $setting) {
        if (($setting['id'] ?? '') === GRACEART_CONTACT_EMAIL_OPTION) {
            array_splice($settings, $index + 1, 0, [$field]);

            return $settings;
        }
    }

    $settings[] = $field;

    return $settings;
}, 20);

/**
 * Path of the PDF given by its media library address, or null when it is not
 * set or not usable.
 */
function graceartTermsCustomPdfPath(): ?string
{
    $url = trim((string) get_option(GRACEART_TERMS_PDF_OPTION, ''));

    if ($url === '') {
        return null;
    }

    $attachment_id = attachment_url_to_postid($url);
    $path = $attachment_id ? get_attached_file($attachment_id) : false;

    if (! $path || strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'pdf' || ! is_readable($path)) {
        return null;
    }

    return $path;
}

/**
 * HTML of the terms page for the PDF.
 */
function graceartTermsPdfHtml(WP_Post $page): string
{
    $content = apply_filters('the_content', $page->post_content);

    return sprintf(
        '<!DOCTYPE html><html lang="sk"><head><meta charset="utf-8"><style>%s</style></head><body><h1>%s</h1>%s<p class="valid">%s</p></body></html>',
        'body{font-family:"Open Sans","DejaVu Sans",sans-serif;font-size:10pt;line-height:1.5;color:#222}'
        . 'h1{font-size:18pt;margin:0 0 12pt}h2{font-size:13pt;margin:16pt 0 4pt}h3{font-size:11pt;margin:12pt 0 4pt}'
        . 'p,li{margin:0 0 6pt}ul,ol{margin:0 0 6pt 16pt;padding:0}a{color:#222}table{border-collapse:collapse}td,th{border:1px solid #999;padding:3pt 5pt}'
        . '.valid{margin-top:18pt;font-size:8pt;color:#666}',
        esc_html(get_the_title($page)),
        $content,
        esc_html(sprintf(
            /* translators: %s: date the terms page was last changed */
            __('Znenie z %s', 'graceart'),
            wp_date('j. n. Y', strtotime($page->post_modified_gmt . ' UTC'))
        ))
    );
}

/**
 * PDF made from the terms page, kept per version of the page: a change of the
 * page makes a new file, and files of older versions are removed.
 */
function graceartTermsPagePdfPath(): ?string
{
    $page_id = (int) get_option('woocommerce_terms_page_id');
    $page = $page_id > 0 ? get_post($page_id) : null;

    if (! $page instanceof WP_Post || $page->post_status !== 'publish' || ! function_exists('wcpdf_get_pdf_maker')) {
        return null;
    }

    $uploads = wp_upload_dir();
    $base = trailingslashit($uploads['basedir']) . 'graceart-shop/';
    $dir = $base . substr(md5($page->post_modified_gmt . '|' . $page->post_content), 0, 12) . '/';
    $path = $dir . 'obchodne-podmienky.pdf';

    if (is_readable($path)) {
        return $path;
    }

    try {
        $pdf = wcpdf_get_pdf_maker(graceartTermsPdfHtml($page), ['paper_size' => 'A4', 'paper_orientation' => 'portrait'])->output();
    } catch (Throwable $e) {
        error_log('graceart-shop: terms PDF failed: ' . $e->getMessage());

        return null;
    }

    if (! $pdf || ! wp_mkdir_p($dir) || file_put_contents($path, $pdf) === false) {
        return null;
    }

    // Older versions of the terms are no longer needed.
    foreach ((array) glob($base . '*', GLOB_ONLYDIR) as $old) {
        if (trailingslashit($old) !== $dir) {
            array_map('unlink', (array) glob($old . '/*'));
            @rmdir($old);
        }
    }

    return $path;
}

/**
 * The terms PDF to attach: the one set by hand, else the one made from the page.
 */
function graceartTermsPdfPath(): ?string
{
    return graceartTermsCustomPdfPath() ?? graceartTermsPagePdfPath();
}

add_filter('woocommerce_email_attachments', function ($attachments, $email_id) {
    if (! in_array($email_id, ['customer_processing_order', 'customer_on_hold_order'], true)) {
        return $attachments;
    }

    $path = graceartTermsPdfPath();

    if ($path !== null) {
        $attachments = array_merge((array) $attachments, [$path]);
    }

    return $attachments;
}, 10, 2);

/**
 * Sender and contact address of the shop's e-mails come from the company
 * details (Kontaktný e-mail), not from a separate WooCommerce field; the same
 * address fills {store_email} in the e-mail texts.
 */
add_filter('woocommerce_email_from_address', function ($address) {
    $contact = graceartContactEmail();

    return $contact !== '' ? $contact : $address;
}, 20);

add_filter('woocommerce_email_from_name', fn ($name) => (string) get_bloginfo('name'), 20);

/**
 * Free shipping reads "Zadarmo". With a zero price WooCommerce prints the name
 * of the shipping method again in the totals (next to the same name used as
 * the label), which in the e-mails looked like a repeated line.
 */
add_filter('woocommerce_order_shipping_to_display', function ($shipping, $order) {
    if ($order instanceof WC_Order && (float) $order->get_shipping_total() === 0.0 && $order->get_shipping_methods()) {
        return esc_html__('Zadarmo', 'graceart');
    }

    return $shipping;
}, 5, 2);
