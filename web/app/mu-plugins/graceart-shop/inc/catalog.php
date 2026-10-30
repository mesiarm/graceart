<?php

/**
 * Catalogue visibility: sold-out products are not listed anywhere (shop,
 * categories, search, related products), and a product whose whole stock is
 * already in the shopper's own cart counts as sold out for that shopper.
 * Products hidden by hand (visibility column in the admin) stay out as well.
 */

/**
 * Whether the shopper can still add anything of this product: the product
 * itself, or for a variable product at least one of its variations.
 */
function graceartHasAddableStock(WC_Product $product): bool
{
    if (! $product->is_type('variable')) {
        return graceartCanAddToCart($product);
    }

    foreach ($product->get_children() as $variation_id) {
        $variation = wc_get_product($variation_id);

        if ($variation instanceof WC_Product && graceartCanAddToCart($variation)) {
            return true;
        }
    }

    return false;
}

/**
 * Products the shopper's own cart has emptied: a one-off piece (or every
 * variation of one) whose whole stock is already in the cart. For that shopper
 * they are sold out, so listings drop them like any other sold-out product.
 * Only products in the cart can qualify, so the check is cheap.
 */
function graceartCartExhaustedProductIds(): array
{
    if (is_admin() || ! function_exists('WC') || ! WC()->cart) {
        return [];
    }

    $items = WC()->cart->get_cart();

    if (! $items) {
        return [];
    }

    static $cache = [];

    $cache_key = md5(wp_json_encode(array_map(function (array $item): array {
        return [(int) ($item['product_id'] ?? 0), (int) ($item['variation_id'] ?? 0), (int) ($item['quantity'] ?? 0)];
    }, array_values($items))));

    if (isset($cache[$cache_key])) {
        return $cache[$cache_key];
    }

    $exhausted = [];

    foreach ($items as $item) {
        $product_id = (int) ($item['product_id'] ?? 0);

        if ($product_id <= 0 || isset($exhausted[$product_id])) {
            continue;
        }

        $product = wc_get_product($product_id);

        if ($product instanceof WC_Product && ! graceartHasAddableStock($product)) {
            $exhausted[$product_id] = true;
        }
    }

    return $cache[$cache_key] = array_keys($exhausted);
}

/**
 * Sold-out products are not listed anywhere — shop, categories, search,
 * related products, homepage newest products. WooCommerce hides them for the
 * catalog queries and is_visible() once this option is on; it is forced here
 * rather than left to the settings screen.
 */
add_filter('pre_option_woocommerce_hide_out_of_stock_items', fn(): string => 'yes');

add_filter('woocommerce_product_is_visible', function (bool $visible, int $product_id): bool {
    if (! $visible || in_array($product_id, graceartCartExhaustedProductIds(), true)) {
        return false;
    }

    $product = wc_get_product($product_id);

    return $product instanceof WC_Product && graceartHasAddableStock($product);
}, 10, 2);

/**
 * Drop cart-exhausted products from the listing query itself, so pages stay
 * full rather than rendering a gap where content-product.php bails out.
 */
function graceartExcludeCartExhaustedFromQuery(WP_Query $query): void
{
    $exhausted = graceartCartExhaustedProductIds();

    if (! $exhausted) {
        return;
    }

    $not_in = $query->get('post__not_in');
    $not_in = is_array($not_in) ? $not_in : [];
    $query->set('post__not_in', array_values(array_unique(array_merge($not_in, $exhausted))));
}

add_action('woocommerce_product_query', 'graceartExcludeCartExhaustedFromQuery');

// The site search only looks for products (the theme redirects to
// ?post_type=product); WooCommerce only strips "hidden from search" products
// from a search query, so sold-out ones are removed here as in the catalogue.
add_action('pre_get_posts', function (WP_Query $query): void {
    if (is_admin() || ! $query->is_main_query() || ! $query->is_search()) {
        return;
    }

    $visibility_terms = wc_get_product_visibility_term_ids();
    $outofstock_term = (int) ($visibility_terms['outofstock'] ?? 0);

    if ($outofstock_term > 0) {
        $tax_query = $query->get('tax_query');
        $tax_query = is_array($tax_query) ? $tax_query : [];
        $tax_query[] = [
            'taxonomy' => 'product_visibility',
            'field' => 'term_taxonomy_id',
            'terms' => [$outofstock_term],
            'operator' => 'NOT IN',
        ];
        $query->set('tax_query', $tax_query);
    }

    graceartExcludeCartExhaustedFromQuery($query);
});
