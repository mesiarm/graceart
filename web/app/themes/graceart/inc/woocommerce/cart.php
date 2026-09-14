<?php

/**
 * Cart & checkout customisations.
 *
 * Cart and checkout are block-based, so the theme's woocommerce/cart/* and
 * woocommerce/checkout/* PHP templates never run. Anything visual here is done
 * with block CSS, and the place-order label through a checkout JS filter.
 */

/**
 * Info lines for the cart: free shipping threshold and card payment.
 * Drop [graceart_cart_info] into the cart page with a Shortcode block.
 */
add_shortcode('graceart_cart_info', function (): string {
    // Nothing to inform about when there is nothing in the cart (the CSS
    // handles the same for a cart emptied without a reload).
    if (function_exists('WC') && WC()->cart && WC()->cart->is_empty()) {
        return '';
    }

    $lines = [];

    $min = function_exists('graceartFreeShippingMinAmount') ? graceartFreeShippingMinAmount() : null;

    if ($min !== null) {
        $lines[] = sprintf(
            '<li class="graceart-cart-info__item"><i class="fas fa-truck"></i><span>%s</span></li>',
            wp_kses_post(sprintf(
                /* translators: %s: formatted minimum order total */
                __('Poštovné zdarma pri objednávkach od %s', 'graceart'),
                '<strong>' . wp_strip_all_tags(wc_price($min)) . '</strong>'
            ))
        );
    }

    if (! function_exists('graceartCardPaymentEnabled') || graceartCardPaymentEnabled()) {
        $icons = '';

        foreach ([
            'visa.svg' => 'Visa',
            'mastercard.svg' => 'Mastercard',
            'googlepay.svg' => 'Google Pay',
            'applepay.svg' => 'Apple Pay',
        ] as $file => $label) {
            $icons .= sprintf(
                '<img src="%s" alt="%s" loading="lazy" decoding="async">',
                esc_url(fullTemplateUri('assets/images/payment/' . $file)),
                esc_attr($label)
            );
        }

        $lines[] = sprintf(
            '<li class="graceart-cart-info__item"><span class="graceart-cart-info__icons">%s</span><span>%s</span></li>',
            $icons,
            esc_html__('Možná okamžitá platba kartou.', 'graceart')
        );
    }

    if (! $lines) {
        return '';
    }

    return '<ul class="graceart-cart-info">' . implode('', $lines) . '</ul>';
});

/**
 * Shipping is chosen at checkout, not in the cart.
 *
 * The cart's order summary loses its shipping line (carrier radios, address
 * calculator), and its total leaves the shipping cost out: WooCommerce is
 * told the cart is not ready to calculate shipping while it serves the cart
 * page — its render, and the Store API calls the cart block makes from it
 * (quantity changes, coupons), which carry the page as their referer.
 * Checkout, the mini-cart and everything else keep calculating shipping as
 * usual.
 */
function graceartServingCartPage(): bool
{
    if (function_exists('is_cart') && is_cart()) {
        return true;
    }

    if (! (defined('REST_REQUEST') && REST_REQUEST)) {
        return false;
    }

    $referer = wp_get_raw_referer();

    if (! $referer) {
        return false;
    }

    $path = static fn (string $url): string => untrailingslashit((string) wp_parse_url($url, PHP_URL_PATH));

    return $path($referer) === $path(graceartCartUrl());
}

add_filter('woocommerce_cart_ready_to_calc_shipping', function (bool $ready): bool {
    return $ready && ! graceartServingCartPage();
});

add_filter('render_block_woocommerce/cart-order-summary-shipping-block', '__return_empty_string');

/**
 * With no shipping rates in the cart, the cart block prints its own
 * "Shipping will be calculated at checkout" under the total. The string is
 * newer than WooCommerce's Slovak translation, so it is translated here, into
 * the translation set the block script loads (a PHP gettext filter would not
 * reach a string translated in JS).
 */
add_filter('load_script_translations', function ($translations, $file, string $handle, string $domain) {
    if ($domain !== 'woocommerce' || ! is_string($translations)) {
        return $translations;
    }

    $source = 'Shipping will be calculated at checkout';

    if (strpos($translations, $source) === false && strpos($translations, '"locale_data"') !== false) {
        $data = json_decode($translations, true);
        // translate.wordpress.org files key the strings under "messages".
        $key = isset($data['locale_data'][$domain]) ? $domain : 'messages';

        if (isset($data['locale_data'][$key]) && is_array($data['locale_data'][$key])) {
            $data['locale_data'][$key][$source] = [__('Doprava sa vypočíta v pokladni', 'graceart')];
            $translations = wp_json_encode($data);
        }
    }

    return $translations;
}, 10, 4);

/**
 * Slovak labels for the cart/checkout block buttons.
 */
add_action('wp_enqueue_scripts', function (): void {
    $on_cart = function_exists('is_cart') && is_cart();
    $on_checkout = function_exists('is_checkout') && is_checkout();

    if (! $on_cart && ! $on_checkout) {
        return;
    }

    $path = fullTemplatePath('assets/js/checkout-blocks.js');

    if (! file_exists($path)) {
        return;
    }

    wp_enqueue_script(
        'graceart-checkout-blocks',
        fullTemplateUri('assets/js/checkout-blocks.js'),
        ['wc-blocks-checkout'],
        graceartAssetVersion('assets/js/checkout-blocks.js'),
        true
    );

    wp_localize_script('graceart-checkout-blocks', 'graceartCheckoutStrings', [
        'placeOrder' => __('Objednať s povinnosťou platby', 'graceart'),
        'proceedToCheckout' => __('Prejsť k objednávke', 'graceart'),
        // Without shipping in it the cart's total is no estimate; checkout
        // keeps WooCommerce's own label.
        'totalLabel' => $on_cart ? __('Suma', 'graceart') : '',
    ]);
}, 20);
