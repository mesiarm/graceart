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

// WooCommerce endpoint option => slug (see WC_Query::init_query_vars()).
const GRACEART_ENDPOINTS = [
    'woocommerce_checkout_pay_endpoint' => 'platba',
    'woocommerce_checkout_order_received_endpoint' => 'objednavka-prijata',
    'woocommerce_myaccount_orders_endpoint' => 'objednavky',
    'woocommerce_myaccount_view_order_endpoint' => 'objednavka',
    'woocommerce_myaccount_downloads_endpoint' => 'na-stiahnutie',
    'woocommerce_myaccount_edit_account_endpoint' => 'upravit-ucet',
    'woocommerce_myaccount_edit_address_endpoint' => 'upravit-adresu',
    'woocommerce_myaccount_payment_methods_endpoint' => 'platobne-metody',
    'woocommerce_myaccount_lost_password_endpoint' => 'zabudnute-heslo',
    'woocommerce_logout_endpoint' => 'odhlasit-sa',
    'woocommerce_myaccount_add_payment_method_endpoint' => 'pridat-platobnu-metodu',
    'woocommerce_myaccount_delete_payment_method_endpoint' => 'odstranit-platobnu-metodu',
    'woocommerce_myaccount_set_default_payment_method_endpoint' => 'predvolena-platobna-metoda',
];

// Blog taxonomy bases (WordPress options).
const GRACEART_TAXONOMY_BASES = [
    'category_base' => 'kategoria',
    'tag_base' => 'stitok',
];

// Bump whenever anything above changes so the rewrite rules get rebuilt once.
const GRACEART_PERMALINKS_VERSION = '1';

foreach (GRACEART_ENDPOINTS + GRACEART_TAXONOMY_BASES as $graceart_option => $graceart_slug) {
    add_filter('pre_option_' . $graceart_option, static fn() => $graceart_slug);
}
unset($graceart_option, $graceart_slug);

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
