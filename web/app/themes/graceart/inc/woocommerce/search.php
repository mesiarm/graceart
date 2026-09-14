<?php

// The site search only looks for products.
add_action('pre_get_posts', function (WP_Query $query): void {
    if (is_admin() || ! $query->is_main_query() || ! $query->is_search()) {
        return;
    }

    $query->set('post_type', 'product');

    // WooCommerce only strips "hidden from search" products from a search
    // query; sold-out ones are hidden the same way as in the catalog.
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
