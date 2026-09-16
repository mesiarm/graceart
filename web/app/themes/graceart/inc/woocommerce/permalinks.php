<?php

/**
 * Slovak URLs everywhere.
 *
 * Page slugs, product permalink bases, WooCommerce endpoints and the WordPress
 * bases (pagination, author, search, blog taxonomies) are all pinned from code,
 * so a fresh database or a "reset" in WooCommerce → Settings can never bring
 * the English defaults (/cart/, /my-account/orders/, /page/2/ ...) back.
 */

// WooCommerce/YITH page option => post_name.
const GRACEART_PAGE_SLUGS = [
    'woocommerce_shop_page_id' => 'produkty',
    'woocommerce_cart_page_id' => 'kosik',
    'woocommerce_checkout_page_id' => 'zhrnutie-objednavky',
    'woocommerce_myaccount_page_id' => 'moj-ucet',
    'woocommerce_refund_returns_page_id' => 'vratenie-tovaru',
    'woocommerce_terms_page_id' => 'obchodne-podmienky',
    'yith_wcwl_wishlist_page_id' => 'zoznam-priani',
];

const GRACEART_PRODUCT_PERMALINKS = [
    'product_base' => 'produkt',
    'category_base' => 'kategoria-produktu',
    'tag_base' => 'stitok-produktu',
];

// WooCommerce endpoint => slug (keys are WC_Query's query var keys).
const GRACEART_ENDPOINTS = [
    'order-pay' => 'platba',
    'order-received' => 'objednavka-prijata',
    'orders' => 'objednavky',
    'view-order' => 'objednavka',
    'downloads' => 'na-stiahnutie',
    'edit-account' => 'upravit-ucet',
    'edit-address' => 'upravit-adresu',
    'payment-methods' => 'platobne-metody',
    'lost-password' => 'zabudnute-heslo',
    'customer-logout' => 'odhlasit-sa',
    'add-payment-method' => 'pridat-platobnu-metodu',
    'delete-payment-method' => 'odstranit-platobnu-metodu',
    'set-default-payment-method' => 'predvolena-platobna-metoda',
];

// Endpoint => option WooCommerce stores it in (see WC_Query::init_query_vars()).
const GRACEART_ENDPOINT_OPTIONS = [
    'order-pay' => 'woocommerce_checkout_pay_endpoint',
    'order-received' => 'woocommerce_checkout_order_received_endpoint',
    'orders' => 'woocommerce_myaccount_orders_endpoint',
    'view-order' => 'woocommerce_myaccount_view_order_endpoint',
    'downloads' => 'woocommerce_myaccount_downloads_endpoint',
    'edit-account' => 'woocommerce_myaccount_edit_account_endpoint',
    'edit-address' => 'woocommerce_myaccount_edit_address_endpoint',
    'payment-methods' => 'woocommerce_myaccount_payment_methods_endpoint',
    'lost-password' => 'woocommerce_myaccount_lost_password_endpoint',
    'customer-logout' => 'woocommerce_logout_endpoint',
    'add-payment-method' => 'woocommerce_myaccount_add_payment_method_endpoint',
    'delete-payment-method' => 'woocommerce_myaccount_delete_payment_method_endpoint',
    'set-default-payment-method' => 'woocommerce_myaccount_set_default_payment_method_endpoint',
];

// Blog taxonomy bases (WordPress options).
const GRACEART_TAXONOMY_BASES = [
    'category_base' => 'kategoria',
    'tag_base' => 'stitok',
];

// Bump whenever anything above changes so the rewrite rules get rebuilt once.
const GRACEART_PERMALINKS_VERSION = '2';

// WC_Query reads the endpoint options in WooCommerce's constructor, before the
// theme is loaded, so the option filters below only cover the admin screen and
// the live query vars have to be swapped through WooCommerce's own filter.
add_filter('woocommerce_get_query_vars', function (array $vars): array {
    return array_merge($vars, GRACEART_ENDPOINTS);
});

foreach (GRACEART_ENDPOINT_OPTIONS as $graceart_endpoint => $graceart_option) {
    add_filter('pre_option_' . $graceart_option, static fn() => GRACEART_ENDPOINTS[$graceart_endpoint]);
}

foreach (GRACEART_TAXONOMY_BASES as $graceart_option => $graceart_slug) {
    add_filter('pre_option_' . $graceart_option, static fn() => $graceart_slug);
}
unset($graceart_endpoint, $graceart_option, $graceart_slug);

add_filter('option_woocommerce_permalinks', function ($permalinks): array {
    return array_merge(is_array($permalinks) ? $permalinks : [], GRACEART_PRODUCT_PERMALINKS);
});

add_filter('default_option_woocommerce_permalinks', function (): array {
    return GRACEART_PRODUCT_PERMALINKS;
});

add_filter('woocommerce_register_post_type_product', function (array $args): array {
    if (($args['has_archive'] ?? 'shop') === 'shop') {
        $args['has_archive'] = GRACEART_PAGE_SLUGS['woocommerce_shop_page_id'];
    }

    return $args;
});

// /page/2/ → /strana/2/, /author/x/ → /autor/x/, /search/x/ → /hladanie/x/.
add_action('init', function () {
    global $wp_rewrite;

    $wp_rewrite->pagination_base = 'strana';
    $wp_rewrite->comments_pagination_base = 'komentare-strana';
    $wp_rewrite->author_base = 'autor';
    $wp_rewrite->search_base = 'hladanie';
}, 1);

// Keep the WooCommerce/YITH pages on their Slovak slugs and rebuild the rewrite
// rules once after any change to the configuration above.
add_action('init', function () {
    $changed = false;

    foreach (GRACEART_PAGE_SLUGS as $option => $slug) {
        $page_id = (int) get_option($option);

        if ($page_id <= 0) {
            continue;
        }

        $page = get_post($page_id);

        if (! $page instanceof WP_Post || $page->post_name === $slug) {
            continue;
        }

        wp_update_post([
            'ID' => $page_id,
            'post_name' => $slug,
        ]);

        $changed = true;
    }

    if ($changed || get_option('graceart_permalinks_version') !== GRACEART_PERMALINKS_VERSION) {
        flush_rewrite_rules(false);
        update_option('graceart_permalinks_version', GRACEART_PERMALINKS_VERSION);
    }
}, 20);
