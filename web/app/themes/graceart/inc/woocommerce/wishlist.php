<?php

add_filter('yith_wcwl_wishlist_view_name_heading', fn(): string => __('Produkt', 'graceart'));
add_filter('yith_wcwl_wishlist_view_price_heading', fn(): string => __('Cena', 'graceart'));
add_filter('yith_wcwl_wishlist_view_quantity_heading', fn(): string => __('Množstvo', 'graceart'));
add_filter('yith_wcwl_wishlist_view_stock_heading', fn(): string => __('Dostupnosť', 'graceart'));
add_filter('yith_wcwl_wishlist_view_arrange_heading', fn(): string => __('Poradie', 'graceart'));
add_filter('yith_wcwl_remove_product_wishlist_message_title', fn(): string => __('Odstrániť tento produkt', 'graceart'));
add_filter('yith_wcwl_out_of_stock_label', fn(): string => __('Nie je skladom', 'graceart'));
add_filter('yith_wcwl_in_stock_label', fn(): string => __('Skladom', 'graceart'));
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
