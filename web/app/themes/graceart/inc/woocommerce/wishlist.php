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
