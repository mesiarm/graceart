<?php

/**
 * Free shipping over a threshold.
 *
 * The threshold lives in the "Poštovné zdarma" (free_shipping) method of each
 * shipping zone in WooCommerce → Settings → Shipping, so every zone can have
 * its own minimum. WooCommerce offers that method only once the cart reaches
 * the zone's threshold. It is never shown as a separate option though: when it
 * is offered, every real shipping method of the package drops to 0 €, so the
 * customer keeps their choice of carrier (and of a Packeta pickup point).
 */
add_filter('woocommerce_package_rates', function (array $rates): array {
    $qualifies = false;

    foreach ($rates as $key => $rate) {
        if ($rate instanceof WC_Shipping_Rate && $rate->get_method_id() === 'free_shipping') {
            $qualifies = true;
            unset($rates[$key]);
        }
    }

    if (! $qualifies) {
        return $rates;
    }

    foreach ($rates as $rate) {
        if ($rate instanceof WC_Shipping_Rate) {
            $rate->set_cost(0);
            $rate->set_taxes([]);
        }
    }

    return $rates;
}, 10, 1);

/**
 * [graceart_doprava_zdarma] – "nad 100 €" podľa nastavenia zón dopravy.
 * Berie najnižšiu hranicu zo spôsobov "Poštovné zdarma"; ak sa hranice zón
 * líšia, dodá "podľa krajiny", aby text nesľuboval viac, než platí.
 */
function graceartFreeShippingThresholds(): array
{
    if (! class_exists('WC_Shipping_Zones')) {
        return [];
    }

    $amounts = [];

    foreach (WC_Shipping_Zones::get_zones() as $zone_data) {
        $zone = new WC_Shipping_Zone($zone_data['zone_id']);

        foreach ($zone->get_shipping_methods(true) as $method) {
            if ($method->id !== 'free_shipping' || ! in_array($method->get_option('requires'), ['min_amount', 'either', 'both'], true)) {
                continue;
            }

            $amount = (float) $method->get_option('min_amount');

            if ($amount > 0) {
                $amounts[] = $amount;
            }
        }
    }

    return array_values(array_unique($amounts));
}

add_shortcode('graceart_doprava_zdarma', function (): string {
    $amounts = graceartFreeShippingThresholds();

    if (! $amounts) {
        return '';
    }

    $text = sprintf('nad %s', html_entity_decode(wp_strip_all_tags(wc_price(min($amounts))), ENT_QUOTES, 'UTF-8'));

    return count($amounts) > 1 ? $text . ' ' . __('(podľa krajiny)', 'graceart') : $text;
});
