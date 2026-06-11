<?php

function graceartMyAccountUrl(): string
{
    if (function_exists('wc_get_page_permalink')) {
        return wc_get_page_permalink('myaccount');
    }

    return home_url('/my-account/');
}

function graceartCartUrl(): string
{
    return function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/');
}

function graceartCheckoutUrl(): string
{
    return function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/checkout/');
}

function graceartCartCount(): int
{
    if (! function_exists('WC') || ! WC()->cart) {
        return 0;
    }

    return WC()->cart->get_cart_contents_count();
}

function graceartWishlistUrl(): string
{
    if (function_exists('yith_wcwl_get_wishlist_url')) {
        return yith_wcwl_get_wishlist_url();
    }

    $wishlist_page = get_page_by_path('wishlist') ?: get_page_by_path('zoznam-priani');

    return $wishlist_page instanceof WP_Post ? get_permalink($wishlist_page) : home_url('/wishlist/');
}

function graceartWishlistCount(): int
{
    if (function_exists('yith_wcwl_wishlists')) {
        $wishlists = yith_wcwl_wishlists();

        if (is_object($wishlists) && method_exists($wishlists, 'count_items_in_wishlist')) {
            return (int) $wishlists->count_items_in_wishlist();
        }
    }

    if (function_exists('yith_wcwl_count_products')) {
        return (int) yith_wcwl_count_products();
    }

    if (function_exists('YITH_WCWL')) {
        $wishlist = YITH_WCWL();

        if (is_object($wishlist) && method_exists($wishlist, 'count_products')) {
            return (int) $wishlist->count_products();
        }
    }

    return 0;
}

add_filter('woocommerce_add_to_cart_fragments', function (array $fragments): array {
    $fragments['span.cart-count'] = '<span class="cart-count">' . esc_html((string) graceartCartCount()) . '</span>';

    return $fragments;
});
