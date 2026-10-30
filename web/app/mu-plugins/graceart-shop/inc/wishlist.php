<?php

/**
 * Wishlist (YITH): behaviour and labels the plugin's Slovak translation lacks.
 * The common column labels and messages are translated by the theme.
 */

// The YITH wishlist table's remaining column labels and messages.
add_filter('yith_wcwl_wishlist_view_quantity_heading', fn(): string => __('Množstvo', 'graceart'));
add_filter('yith_wcwl_wishlist_view_arrange_heading', fn(): string => __('Poradie', 'graceart'));
add_filter('yith_wcwl_move_to_another_list_label', fn(): string => __('Presunúť do iného zoznamu', 'graceart'));
add_filter('yith_wcwl_no_product_to_remove_message', fn(): string => __('V zozname prianí zatiaľ nie sú žiadne produkty.', 'graceart'));

/**
 * YITH removes the item on "init"; drop the query string so a refresh does
 * not repeat the request. No notice: the heart icon going hollow is enough.
 */
add_action('template_redirect', function (): void {
    if (! isset($_GET['remove_from_wishlist'], $_GET['_wpnonce'])) {
        return;
    }

    if (! wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'remove_from_wishlist')) {
        return;
    }

    wp_safe_redirect(remove_query_arg(['remove_from_wishlist', '_wpnonce']));
    exit;
});
