<?php

add_action('after_setup_theme', function () {
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    add_theme_support('wc-product-gallery-zoom');
});

add_action('init', function () {
    if (! function_exists('wc_get_page_id')) {
        return;
    }

    $shop_page_id = wc_get_page_id('shop');

    if ($shop_page_id <= 0) {
        return;
    }

    $shop_page = get_post($shop_page_id);

    if (! $shop_page instanceof WP_Post || $shop_page->post_name === 'produkty') {
        return;
    }

    wp_update_post([
        'ID' => $shop_page_id,
        'post_name' => 'produkty',
    ]);

    flush_rewrite_rules(false);
});

// Drop empty decimals: "70,00 €" reads as "70 €", while "12,50 €" keeps its cents.
add_filter('woocommerce_price_trim_zeros', '__return_true');

add_filter('woocommerce_register_post_type_product', function (array $args): array {
    if (($args['has_archive'] ?? 'shop') === 'shop') {
        $args['has_archive'] = 'produkty';
    }

    return $args;
});
