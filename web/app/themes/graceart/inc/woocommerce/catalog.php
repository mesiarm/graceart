<?php

function graceartCatalogOrderingOptions(): array
{
    return [
        'popularity' => __('Zoradiť podľa popularity', 'graceart'),
        'date' => __('Zoradiť od najnovších', 'graceart'),
        'rating' => __('Zoradiť podľa hodnotenia', 'graceart'),
        'price' => __('Zoradiť podľa ceny: od najnižšej', 'graceart'),
        'price-desc' => __('Zoradiť podľa ceny: od najvyššej', 'graceart'),
    ];
}

add_filter('woocommerce_catalog_orderby', 'graceartCatalogOrderingOptions');

function graceartCatalogTerms(string $taxonomy): array
{
    if (! taxonomy_exists($taxonomy)) {
        return [];
    }

    $terms = get_terms([
        'taxonomy' => $taxonomy,
        'hide_empty' => true,
    ]);

    return is_wp_error($terms) ? [] : $terms;
}

function graceartColorHex(WP_Term $term): string
{
    $hex = get_term_meta($term->term_id, 'graceart_color_hex', true);

    return $hex ? (string) $hex : '#cccccc';
}

function graceartCatalogBrandTaxonomy(): string
{
    foreach (['product_brand', 'pa_brand'] as $taxonomy) {
        if (taxonomy_exists($taxonomy)) {
            return $taxonomy;
        }
    }

    return '';
}
