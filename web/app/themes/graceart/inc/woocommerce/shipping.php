<?php

/**
 * Free shipping over a threshold.
 *
 * The threshold lives in the "Poštovné zdarma" (free_shipping) methods in
 * WooCommerce → Settings → Shipping — that method is the admin-editable carrier
 * for the minimum amount. It is never offered as a separate option though:
 * once the cart reaches the threshold, every real shipping method simply drops
 * to 0 €, so the customer keeps their choice of carrier.
 */

/**
 * Cart amount that counts towards the free shipping threshold, following the
 * same rules as WC_Shipping_Method_Free_Shipping::is_available().
 */
function graceartFreeShippingQualifyingTotal(): float
{
    if (! function_exists('WC') || ! WC()->cart) {
        return 0.0;
    }

    $cart = WC()->cart;
    $total = (float) $cart->get_displayed_subtotal() - (float) $cart->get_discount_total();

    if ($cart->display_prices_including_tax()) {
        $total -= (float) $cart->get_discount_tax();
    }

    return round($total, wc_get_price_decimals());
}

function graceartCartQualifiesForFreeShipping(): bool
{
    $min = graceartFreeShippingMinAmount();

    return $min !== null && graceartFreeShippingQualifyingTotal() >= $min;
}

add_filter('woocommerce_package_rates', function (array $rates): array {
    if (graceartFreeShippingMinAmount() === null) {
        return $rates;
    }

    // Drop the free_shipping rate itself — it only carries the threshold.
    foreach ($rates as $key => $rate) {
        if ($rate instanceof WC_Shipping_Rate && $rate->get_method_id() === 'free_shipping') {
            unset($rates[$key]);
        }
    }

    if (! graceartCartQualifiesForFreeShipping()) {
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
