<?php

/**
 * Terms and conditions (PDF) attached to the order confirmation e-mails.
 *
 * The PDF is a file in the media library; its address is entered in
 * WooCommerce → Settings → General, under the company details. Without it
 * nothing is attached. It goes to the e-mails a customer gets right after
 * ordering: processing (paid, card) and on hold (bank transfer).
 */

const GRACEART_TERMS_PDF_OPTION = 'graceart_terms_pdf_url';

add_filter('woocommerce_general_settings', function (array $settings): array {
    $field = [
        'title' => __('Obchodné podmienky (PDF)', 'graceart'),
        'desc' => __('Adresa PDF súboru z Médií (Médiá → Pridať nový → skopírujte „URL súboru"). Prikladá sa k e-mailu s potvrdením objednávky. Prázdne = nič sa neprikladá.', 'graceart'),
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
 * Path of the terms PDF on disk, or null when it is not set or not usable.
 */
function graceartTermsPdfPath(): ?string
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
