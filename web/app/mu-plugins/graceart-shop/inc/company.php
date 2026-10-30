<?php

/**
 * Company identifiers (IČO / DIČ / IČ DPH) stored alongside the WooCommerce
 * store address, so the invoice has a single source of truth for them.
 *
 * WooCommerce → Settings → General, right under "Store Address".
 */

const GRACEART_ICO_OPTION = 'graceart_company_ico';
const GRACEART_DIC_OPTION = 'graceart_company_dic';
const GRACEART_VAT_OPTION = 'graceart_company_vat_id';
const GRACEART_CONTACT_EMAIL_OPTION = 'graceart_contact_email';

add_filter('woocommerce_general_settings', function (array $settings): array {
    $fields = [
        [
            'title' => __('IČO', 'graceart'),
            'desc' => __('Identifikačné číslo organizácie. Zobrazuje sa na faktúre.', 'graceart'),
            'id' => GRACEART_ICO_OPTION,
            'type' => 'text',
            'desc_tip' => true,
        ],
        [
            'title' => __('DIČ', 'graceart'),
            'desc' => __('Daňové identifikačné číslo. Zobrazuje sa na faktúre.', 'graceart'),
            'id' => GRACEART_DIC_OPTION,
            'type' => 'text',
            'desc_tip' => true,
        ],
        [
            'title' => __('IČ DPH', 'graceart'),
            'desc' => __('Vyplňte iba ak ste platcom DPH. Ak zostane prázdne, na faktúre sa uvedie "Dodávateľ nie je platcom DPH."', 'graceart'),
            'id' => GRACEART_VAT_OPTION,
            'type' => 'text',
            'desc_tip' => true,
        ],
        [
            'title' => __('Kontaktný e-mail', 'graceart'),
            'desc' => __('Zobrazuje sa na stránke Kontakt a chodia naň správy z kontaktného formulára.', 'graceart'),
            'id' => GRACEART_CONTACT_EMAIL_OPTION,
            'type' => 'email',
            'desc_tip' => true,
        ],
    ];

    // Insert after the store address block, which ends with the store postcode.
    $offset = null;

    foreach ($settings as $index => $setting) {
        if (($setting['id'] ?? '') === 'woocommerce_store_postcode') {
            $offset = $index + 1;
            break;
        }
    }

    if ($offset === null) {
        array_splice($settings, count($settings), 0, $fields);

        return $settings;
    }

    array_splice($settings, $offset, 0, $fields);

    return $settings;
});

function graceartCompanyIco(): string
{
    return trim((string) get_option(GRACEART_ICO_OPTION, ''));
}

function graceartCompanyDic(): string
{
    return trim((string) get_option(GRACEART_DIC_OPTION, ''));
}

function graceartCompanyVatId(): string
{
    return trim((string) get_option(GRACEART_VAT_OPTION, ''));
}

function graceartCompanyIsVatRegistered(): bool
{
    return graceartCompanyVatId() !== '';
}

/**
 * Public contact address, falling back to the WooCommerce sender address and
 * then the site admin address.
 */
function graceartContactEmail(): string
{
    $candidates = [
        (string) get_option(GRACEART_CONTACT_EMAIL_OPTION, ''),
        (string) get_option('woocommerce_email_from_address', ''),
        (string) get_option('admin_email', ''),
    ];

    foreach ($candidates as $email) {
        $email = sanitize_email(trim($email));

        if ($email !== '' && is_email($email)) {
            return $email;
        }
    }

    return '';
}

/**
 * Store address from the WooCommerce settings, one entry per line, with the
 * postcode and city combined the way Slovak invoices print them.
 */
function graceartCompanyAddressLines(): array
{
    $street = trim((string) get_option('woocommerce_store_address', ''));
    $city = trim((string) get_option('woocommerce_store_city', ''));

    // The default country is always set, so on its own it does not count as a
    // configured address — return nothing and let the caller fall back.
    if ($street === '' && $city === '') {
        return [];
    }

    $city_line = trim(sprintf(
        '%s %s',
        (string) get_option('woocommerce_store_postcode', ''),
        $city
    ));

    $country_code = (string) get_option('woocommerce_default_country', '');
    $country_code = explode(':', $country_code)[0];
    $country = '';

    if ($country_code !== '' && function_exists('WC') && ! empty(WC()->countries)) {
        $countries = WC()->countries->get_countries();
        $country = $countries[$country_code] ?? '';
    }

    $lines = [
        $street,
        (string) get_option('woocommerce_store_address_2', ''),
        $city_line,
        $country,
    ];

    return array_values(array_filter(array_map('trim', $lines)));
}
