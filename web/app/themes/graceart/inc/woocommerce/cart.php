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
    $lines = [];

    $min = function_exists('graceartFreeShippingMinAmount') ? graceartFreeShippingMinAmount() : null;

    if ($min !== null) {
        $lines[] = sprintf(
            '<li class="graceart-cart-info__item"><i class="fas fa-truck"></i><span>%s</span></li>',
            wp_kses_post(sprintf(
                /* translators: %s: formatted minimum order total */
                __('Pri nákupe nad %s poštovné neplatíte!', 'graceart'),
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
                '<img src="%s" alt="%s">',
                esc_url(fullTemplateUri('assets/images/payment/' . $file)),
                esc_attr($label)
            );
        }

        $lines[] = sprintf(
            '<li class="graceart-cart-info__item"><span class="graceart-cart-info__icons">%s</span><span>%s</span></li>',
            $icons,
            esc_html__('Možná platba kartou', 'graceart')
        );
    }

    if (! $lines) {
        return '';
    }

    return '<ul class="graceart-cart-info">' . implode('', $lines) . '</ul>';
});

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
        'placeOrder' => __('Objednať', 'graceart'),
        'proceedToCheckout' => __('Prejsť k objednávke', 'graceart'),
    ]);
}, 20);
