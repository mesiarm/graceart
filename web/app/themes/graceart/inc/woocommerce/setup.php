<?php

add_action('after_setup_theme', function () {
    // Without this WordPress prints no <title> at all and browsers fall back to
    // showing the URL.
    add_theme_support('title-tag');
    add_theme_support('woocommerce');
    // No wc-product-gallery-* supports: the theme renders its own gallery with
    // slick + PhotoSwipe, so WooCommerce's flexslider/PhotoSwipe would load twice.
});

// Drop empty decimals: "70,00 €" reads as "70 €", while "12,50 €" keeps its cents.
add_filter('woocommerce_price_trim_zeros', '__return_true');
