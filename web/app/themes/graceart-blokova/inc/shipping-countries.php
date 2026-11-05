<?php

/**
 * Countries the shop delivers to.
 *
 * Pickup points (Packeta) are handled by the Packeta plugin. What stays
 * here is shop-specific: the country dropdowns offer only the countries of
 * the shipping zones, the checkout points a customer from elsewhere to the
 * contact form, and a change of country reaches the server at once so the
 * shipping options follow it (assets/js/checkout-countries.js).
 */

const GRACEART_SHIPPING_NAMESPACE = 'graceart-shipping';

/**
 * Countries the shop delivers to: those of the shipping zones that have an
 * enabled method. Empty (no restriction) when no zone is set up.
 *
 * @return array<int, string> Upper-case ISO codes.
 */
function graceartShippingCountries(): array
{
    static $countries = null;

    if ($countries !== null) {
        return $countries;
    }

    $countries = [];

    if (! class_exists('WC_Shipping_Zones') || ! function_exists('WC') || ! WC()->countries) {
        return $countries;
    }

    $continents = WC()->countries->get_continents();

    foreach (WC_Shipping_Zones::get_zones() as $zone) {
        $has_method = false;

        foreach ($zone['shipping_methods'] ?? [] as $method) {
            if ($method instanceof WC_Shipping_Method && $method->is_enabled()) {
                $has_method = true;
            }
        }

        if (! $has_method) {
            continue;
        }

        foreach ($zone['zone_locations'] ?? [] as $location) {
            if ($location->type === 'country') {
                $countries[] = $location->code;
            } elseif ($location->type === 'state') {
                $countries[] = strtok($location->code, ':');
            } elseif ($location->type === 'continent') {
                $countries = array_merge($countries, $continents[$location->code]['countries'] ?? []);
            }
        }
    }

    $countries = array_values(array_unique($countries));

    return $countries;
}

/**
 * The country dropdowns of the storefront offer only Packeta countries; the
 * admin keeps the full list for orders entered by hand.
 */
function graceartShippingLimitCountries(array $countries): array
{
    $allowed = is_admin() ? [] : graceartShippingCountries();

    $countries = $allowed ? array_intersect_key($countries, array_flip($allowed)) : $countries;

    // Slovakia, the home market, goes first.
    return isset($countries['SK']) ? ['SK' => $countries['SK']] + $countries : $countries;
}

add_filter('woocommerce_countries_allowed_countries', 'graceartShippingLimitCountries');
add_filter('woocommerce_countries_shipping_countries', 'graceartShippingLimitCountries');

/**
 * Contact page (template "Kontakt"), where a customer from another country
 * asks whether the order can be delivered to them.
 */
function graceartShippingContactUrl(): string
{
    $pages = get_pages([
        'meta_key' => '_wp_page_template',
        'meta_value' => 'templates/contact.php',
        'number' => 1,
    ]);

    $page = $pages ? $pages[0] : get_page_by_path('kontakt');

    $url = $page ? get_permalink($page) : home_url('/');

    return add_query_arg('doprava', 'ina-krajina', $url) . '#kontakt-formular';
}

/**
 * (Registered on init: the theme loads after woocommerce_blocks_loaded.)
 * The checkout script hands a changed country to the server through the
 * Store API's cart/extensions endpoint (extensionCartUpdate); shipping is
 * recalculated by the Store API after this callback.
 */
add_action('init', function (): void {
    if (! function_exists('woocommerce_store_api_register_update_callback')) {
        return;
    }

    woocommerce_store_api_register_update_callback([
        'namespace' => GRACEART_SHIPPING_NAMESPACE,
        'callback' => function ($data): void {
            if (! function_exists('WC') || ! WC()->customer || ! is_array($data)) {
                return;
            }

            if (isset($data['country']) && is_string($data['country'])) {
                $country = strtoupper(sanitize_text_field($data['country']));

                if (array_key_exists($country, WC()->countries->get_shipping_countries())) {
                    WC()->customer->set_shipping_country($country);
                    WC()->customer->set_shipping_state('');
                    WC()->customer->set_shipping_postcode('');
                }
            }
        },
    ]);
});

add_action('wp_enqueue_scripts', function (): void {
    if (! function_exists('is_checkout') || ! is_checkout() || is_wc_endpoint_url('order-received')) {
        return;
    }

    if (! graceartShippingCountries()) {
        return;
    }

    $path = 'assets/js/checkout-countries.js';

    if (! file_exists(get_theme_file_path($path))) {
        return;
    }

    wp_enqueue_script(
        'graceart-checkout-countries',
        get_theme_file_uri($path),
        ['wc-blocks-checkout', 'wp-plugins', 'wp-element', 'wp-data'],
        (string) filemtime(get_theme_file_path($path)),
        true,
    );

    wp_enqueue_style(
        'graceart-checkout-countries',
        get_theme_file_uri('assets/css/checkout-countries.css'),
        [],
        (string) filemtime(get_theme_file_path('assets/css/checkout-countries.css')),
    );

    wp_localize_script('graceart-checkout-countries', 'graceartCountries', [
        'otherCountryUrl' => graceartShippingContactUrl(),
        'strings' => [
            'otherCountry' => __('Nenašli ste svoju krajinu?', 'graceart'),
            'otherCountryLink' => __('Opýtajte sa, či vám vieme poslať objednávku aj inam.', 'graceart'),
        ],
    ]);
}, 20);
