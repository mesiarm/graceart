<?php

// The site search only looks for products.
add_action('pre_get_posts', function (WP_Query $query): void {
    if (is_admin() || ! $query->is_main_query() || ! $query->is_search()) {
        return;
    }

    $query->set('post_type', 'product');
});
