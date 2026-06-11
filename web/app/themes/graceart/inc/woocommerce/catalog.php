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
