<?php

/**
 * Packeta pickup points (Z-BOX / výdajné miesto).
 *
 * Packeta is an ordinary flat-rate method in the shipping zones; the
 * "Packeta – výber výdajného miesta" checkbox on such a method and the API
 * key under WooCommerce → Settings → Shipping → Shipping options turn on the
 * pickup point picker for it. The picker is the Packeta widget, opened from
 * a slot fill under the checkout block's shipping options
 * (assets/js/checkout-packeta.js). The chosen point is kept in the customer
 * session (keyed by the rate it was picked for, so a change of country or
 * carrier drops it), required when the order is placed through the Store
 * API, and then stored on the order and its shipping line.
 */

use Automattic\WooCommerce\StoreApi\Exceptions\RouteException;

const GRACEART_PACKETA_API_KEY_OPTION = 'graceart_packeta_api_key';
const GRACEART_PACKETA_INSTANCE_OPTION = 'graceart_packeta';
const GRACEART_PACKETA_SESSION_KEY = 'graceart_packeta_point';
const GRACEART_PACKETA_ORDER_META = '_graceart_packeta_point';
const GRACEART_PACKETA_NAMESPACE = 'graceart-packeta';

/*
|--------------------------------------------------------------------------
| Settings
|--------------------------------------------------------------------------
*/

add_filter('woocommerce_shipping_settings', function (array $settings): array {
    $settings[] = [
        'title' => __('Packeta', 'graceart'),
        'type' => 'title',
        'desc' => __('Výber výdajného miesta (Z-BOX alebo pobočky) v pokladni. Zapína sa pri jednotlivých spôsoboch dopravy v dopravných zónach.', 'graceart'),
        'id' => 'graceart_packeta_options',
    ];

    $settings[] = [
        'title' => __('API kľúč Packeta', 'graceart'),
        'desc' => __('Kľúč pre widget výdajných miest z klientskej sekcie Packeta (Nastavenia → Klientská podpora → API). Bez neho sa výber miesta v pokladni nezobrazí.', 'graceart'),
        'id' => GRACEART_PACKETA_API_KEY_OPTION,
        'type' => 'text',
        'desc_tip' => true,
    ];

    $settings[] = [
        'type' => 'sectionend',
        'id' => 'graceart_packeta_options',
    ];

    return $settings;
});

add_filter('woocommerce_shipping_instance_form_fields_flat_rate', function (array $fields): array {
    $fields[GRACEART_PACKETA_INSTANCE_OPTION] = [
        'title' => __('Packeta', 'graceart'),
        'type' => 'checkbox',
        'label' => __('Zákazník si vyberie výdajné miesto Packeta (Z-BOX alebo pobočku)', 'graceart'),
        'description' => __('V pokladni sa pri tejto doprave zobrazí výber výdajného miesta a bez neho objednávku nejde dokončiť. Vyžaduje API kľúč Packeta v Možnostiach dopravy.', 'graceart'),
        'default' => 'no',
    ];

    return $fields;
});

function graceartPacketaApiKey(): string
{
    return trim((string) get_option(GRACEART_PACKETA_API_KEY_OPTION, ''));
}

/**
 * Rate IDs ("flat_rate:2") of the enabled shipping methods with the pickup
 * point picker on. Empty without an API key: the customer could not pick a
 * point, so none may be demanded.
 *
 * @return array<int, string>
 */
function graceartPacketaRateIds(): array
{
    static $ids = null;

    if ($ids !== null) {
        return $ids;
    }

    $ids = [];

    if (graceartPacketaApiKey() === '' || ! class_exists('WC_Shipping_Zones')) {
        return $ids;
    }

    $zones = WC_Shipping_Zones::get_zones();
    $zones[] = ['shipping_methods' => (new WC_Shipping_Zone(0))->get_shipping_methods()];

    foreach ($zones as $zone) {
        foreach ($zone['shipping_methods'] ?? [] as $method) {
            if (
                $method instanceof WC_Shipping_Method
                && $method->is_enabled()
                && $method->get_instance_option(GRACEART_PACKETA_INSTANCE_OPTION) === 'yes'
            ) {
                $ids[] = $method->get_rate_id();
            }
        }
    }

    return $ids;
}

/*
|--------------------------------------------------------------------------
| Pickup point data
|--------------------------------------------------------------------------
*/

/**
 * Keep only the widget fields the shop needs, as plain text.
 *
 * @param mixed $raw The point object the Packeta widget returned.
 * @return array<string, string>|null
 */
function graceartPacketaSanitizePoint($raw): ?array
{
    if (! is_array($raw)) {
        return null;
    }

    $text = static fn(string $key): string => isset($raw[$key]) && is_scalar($raw[$key]) ? sanitize_text_field((string) $raw[$key]) : '';

    $point = [
        'id' => $text('id'),
        'name' => $text('name'),
        'place' => $text('place'),
        'street' => $text('street'),
        'city' => $text('city'),
        'zip' => $text('zip'),
        'country' => strtolower($text('country')),
        'carrierId' => $text('carrierId'),
        'carrierPickupPointId' => $text('carrierPickupPointId'),
        'pickupPointType' => $text('pickupPointType'),
        'url' => isset($raw['url']) && is_string($raw['url']) ? esc_url_raw($raw['url']) : '',
    ];

    if ($point['id'] === '' || $point['name'] === '') {
        return null;
    }

    return $point;
}

/**
 * Name and address of a point, each once (the name often already carries
 * the street).
 *
 * @param array<string, string> $point
 * @return array<int, string>
 */
function graceartPacketaPointLines(array $point): array
{
    $address = trim(($point['street'] ?? '') . ', ' . trim(($point['zip'] ?? '') . ' ' . ($point['city'] ?? '')), ', ');

    return array_values(array_unique(array_filter([$point['name'] ?? '', $address])));
}

function graceartPacketaPointLabel(array $point): string
{
    return implode(', ', graceartPacketaPointLines($point));
}

/**
 * @return array{rate_id: string, point: array<string, string>}|null
 */
function graceartPacketaSessionPoint(): ?array
{
    if (! function_exists('WC') || ! WC()->session) {
        return null;
    }

    $saved = WC()->session->get(GRACEART_PACKETA_SESSION_KEY);

    if (! is_array($saved) || empty($saved['rate_id']) || empty($saved['point']) || ! is_array($saved['point'])) {
        return null;
    }

    return ['rate_id' => (string) $saved['rate_id'], 'point' => $saved['point']];
}

/**
 * @return array<string, string>|null
 */
function graceartPacketaOrderPoint(WC_Order $order): ?array
{
    $point = $order->get_meta(GRACEART_PACKETA_ORDER_META);

    return is_array($point) && ! empty($point['name']) ? $point : null;
}

/*
|--------------------------------------------------------------------------
| Checkout
|--------------------------------------------------------------------------
*/

/**
 * The checkout script saves the chosen point through the Store API's
 * cart/extensions endpoint (extensionCartUpdate).
 */
add_action('woocommerce_blocks_loaded', function (): void {
    if (! function_exists('woocommerce_store_api_register_update_callback')) {
        return;
    }

    woocommerce_store_api_register_update_callback([
        'namespace' => GRACEART_PACKETA_NAMESPACE,
        'callback' => function ($data): void {
            if (! WC()->session) {
                return;
            }

            $rate_id = is_array($data) && isset($data['rate_id']) && is_string($data['rate_id']) ? sanitize_text_field($data['rate_id']) : '';
            $point = is_array($data) ? graceartPacketaSanitizePoint($data['point'] ?? null) : null;

            if ($point === null || ! in_array($rate_id, graceartPacketaRateIds(), true)) {
                WC()->session->set(GRACEART_PACKETA_SESSION_KEY, null);

                return;
            }

            WC()->session->set(GRACEART_PACKETA_SESSION_KEY, ['rate_id' => $rate_id, 'point' => $point]);
        },
    ]);
});

/**
 * Placing the order: a Packeta shipping line needs a point picked for that
 * very rate. The point goes onto the order, and onto the shipping line as
 * visible meta for the admin order screen (with the ID, which the Packeta
 * client section asks for when the packet is created).
 *
 * Draft orders are reused within a session, so a point left over from an
 * earlier carrier choice is removed again.
 */
add_action('woocommerce_store_api_checkout_update_order_from_request', function (WC_Order $order): void {
    $rate_ids = graceartPacketaRateIds();
    $saved = graceartPacketaSessionPoint();
    $meta_key = __('Výdajné miesto', 'graceart');
    $has_point = false;

    foreach ($order->get_shipping_methods() as $item) {
        $rate_id = $item->get_method_id() . ':' . $item->get_instance_id();

        if (! in_array($rate_id, $rate_ids, true)) {
            $item->delete_meta_data($meta_key);
            continue;
        }

        if ($saved === null || $saved['rate_id'] !== $rate_id) {
            throw new RouteException(
                'graceart_packeta_point_missing',
                esc_html__('Vyberte prosím výdajné miesto Packeta.', 'graceart'),
                400,
            );
        }

        $has_point = true;
        $order->update_meta_data(GRACEART_PACKETA_ORDER_META, $saved['point']);
        $item->update_meta_data($meta_key, sprintf('%s (ID %s)', graceartPacketaPointLabel($saved['point']), $saved['point']['id']));
    }

    if (! $has_point) {
        $order->delete_meta_data(GRACEART_PACKETA_ORDER_META);
    }
});

// The cart is emptied once the order goes through; the point went with it.
add_action('woocommerce_cart_emptied', function (): void {
    if (function_exists('WC') && WC()->session) {
        WC()->session->set(GRACEART_PACKETA_SESSION_KEY, null);
    }
});

add_action('wp_enqueue_scripts', function (): void {
    if (! function_exists('is_checkout') || ! is_checkout() || is_wc_endpoint_url('order-received')) {
        return;
    }

    $rate_ids = graceartPacketaRateIds();

    if (! $rate_ids) {
        return;
    }

    $path = 'assets/js/checkout-packeta.js';

    if (! file_exists(fullTemplatePath($path))) {
        return;
    }

    wp_enqueue_script(
        'graceart-checkout-packeta',
        fullTemplateUri($path),
        ['wc-blocks-checkout', 'wp-plugins', 'wp-element', 'wp-data'],
        graceartAssetVersion($path),
        true,
    );

    $saved = graceartPacketaSessionPoint();

    wp_localize_script('graceart-checkout-packeta', 'graceartPacketa', [
        'apiKey' => graceartPacketaApiKey(),
        'rateIds' => $rate_ids,
        'language' => substr(function_exists('determine_locale') ? determine_locale() : get_locale(), 0, 2),
        'point' => $saved ? ['rateId' => $saved['rate_id'], 'point' => $saved['point']] : null,
        'strings' => [
            'label' => __('Výdajné miesto Packeta', 'graceart'),
            'hint' => __('Zvoľte Z-BOX alebo výdajné miesto, kde si balík vyzdvihnete.', 'graceart'),
            'choose' => __('Vybrať výdajné miesto', 'graceart'),
            'change' => __('Zmeniť', 'graceart'),
            'required' => __('Vyberte prosím výdajné miesto Packeta.', 'graceart'),
            'widgetFailed' => __('Výber výdajných miest sa nepodarilo načítať. Skúste to prosím znova.', 'graceart'),
            'saveFailed' => __('Výdajné miesto sa nepodarilo uložiť. Skúste ho prosím vybrať znova.', 'graceart'),
        ],
    ]);
}, 20);

/*
|--------------------------------------------------------------------------
| Order display
|--------------------------------------------------------------------------
*/

/**
 * The shipping row of the order totals (emails, order-received page, My
 * Account) says where the packet goes.
 */
add_filter('woocommerce_get_order_item_totals', function (array $rows, WC_Order $order): array {
    if (! isset($rows['shipping'])) {
        return $rows;
    }

    $point = graceartPacketaOrderPoint($order);

    if ($point === null) {
        return $rows;
    }

    $rows['shipping']['value'] .= sprintf(
        '<br><small class="graceart-packeta-point">%s: %s</small>',
        esc_html__('Výdajné miesto', 'graceart'),
        esc_html(graceartPacketaPointLabel($point)),
    );

    return $rows;
}, 10, 2);
