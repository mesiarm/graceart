<?php

add_filter('yith_wcwl_wishlist_view_name_heading', fn(): string => __('Produkt', 'graceart'));
add_filter('yith_wcwl_wishlist_view_price_heading', fn(): string => __('Cena', 'graceart'));
add_filter('yith_wcwl_wishlist_view_quantity_heading', fn(): string => __('Množstvo', 'graceart'));
add_filter('yith_wcwl_wishlist_view_stock_heading', fn(): string => __('Dostupnosť', 'graceart'));
add_filter('yith_wcwl_wishlist_view_arrange_heading', fn(): string => __('Poradie', 'graceart'));
add_filter('yith_wcwl_remove_product_wishlist_message_title', fn(): string => __('Odstrániť tento produkt', 'graceart'));

// The "Dostupnosť" column says what the product page does ("Skladom 3 ks",
// "Na objednávku do 2 týždňov"), for the variant the row's price is for.
add_filter('yith_wcwl_stock_status', function (string $html, $item): string {
    $product = is_object($item) && method_exists($item, 'get_product') ? $item->get_product() : null;

    if (! $product instanceof WC_Product || ! function_exists('graceartAvailabilityShortLabel')) {
        return $html;
    }

    $product = graceartProductLoopDisplayProduct($product);
    $short = graceartAvailabilityShortLabel($product);
    $class = $short['in_stock'] ? 'wishlist-in-stock' : ($product->is_in_stock() ? 'wishlist-on-backorder' : 'wishlist-out-of-stock');

    return '<span class="' . esc_attr($class) . '">' . esc_html(graceartAvailabilityText($product)) . '</span>';
}, 10, 2);

// One wishlist table on every device: the theme lays the rows out with flex
// and wraps the cells on phones, so YITH's separate mobile template (which
// has its own hardcoded stock labels) is not used.
add_filter('yith_wcwl_is_wishlist_responsive', '__return_false');
add_filter('yith_wcwl_move_to_another_list_label', fn(): string => __('Presunúť do iného zoznamu', 'graceart'));
add_filter('yith_wcwl_no_product_to_remove_message', fn(): string => __('V zozname prianí zatiaľ nie sú žiadne produkty.', 'graceart'));

// A variable product shows the price of its first variation, as on the product
// cards, rather than WooCommerce's "70 € – 85 €" range.
add_filter('yith_wcwl_item_formatted_price', function (string $formatted_price, $base_price, WC_Product $product): string {
    if (! $product->is_type('variable') || ! function_exists('graceartProductLoopPriceHtml')) {
        return $formatted_price;
    }

    return graceartProductLoopPriceHtml($product);
}, 10, 3);

// The page band already says "Zoznam prianí"; no second heading over the table.
add_filter('yith_wcwl_wishlist_title', fn(): string => '');

// No "added to wishlist" notice either; the heart icon turns solid instead.
add_filter('yith_wcwl_product_added_to_wishlist_message', '__return_empty_string');
