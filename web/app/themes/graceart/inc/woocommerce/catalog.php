<?php

function graceartCatalogOrderingOptions(): array
{
    return [
        'menu_order' => __('Predvolené zoradenie', 'graceart'),
        'popularity' => __('Zoradiť podľa popularity', 'graceart'),
        'rating' => __('Zoradiť podľa hodnotenia', 'graceart'),
        'date' => __('Zoradiť od najnovších', 'graceart'),
        'price' => __('Zoradiť podľa ceny: od najnižšej', 'graceart'),
        'price-desc' => __('Zoradiť podľa ceny: od najvyššej', 'graceart'),
    ];
}

add_filter('woocommerce_catalog_orderby', 'graceartCatalogOrderingOptions');

function graceartCatalogPriceRanges(): array
{
    return [
        ['min' => null, 'max' => null],
        ['min' => 0, 'max' => 80],
        ['min' => 80, 'max' => 160],
        ['min' => 160, 'max' => 240],
        ['min' => 240, 'max' => 320],
        ['min' => 320, 'max' => null],
    ];
}

function graceartCatalogPriceRangeUrl(?int $min, ?int $max): string
{
    $url = remove_query_arg(['min_price', 'max_price', 'paged']);

    if ($min !== null) {
        $url = add_query_arg('min_price', $min, $url);
    }

    if ($max !== null) {
        $url = add_query_arg('max_price', $max, $url);
    }

    return $url;
}

function graceartCatalogPriceRangeLabel(?int $min, ?int $max): string
{
    if ($min === null && $max === null) {
        return __('Všetko', 'graceart');
    }

    if ($max === null) {
        return wc_price($min) . ' +';
    }

    return wc_price($min) . ' - ' . wc_price($max);
}

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

function graceartCatalogBrandTaxonomy(): string
{
    foreach (['product_brand', 'pa_brand'] as $taxonomy) {
        if (taxonomy_exists($taxonomy)) {
            return $taxonomy;
        }
    }

    return '';
}
