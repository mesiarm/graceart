<?php

/**
 * Product administration: ordering of variations, hidden products, columns in
 * the product list (sales, views, visibility) and the upload limit.
 */

function graceartOrderVariationBySize(int $post_id, WP_Post $post): void
{
    if (wp_is_post_revision($post_id) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
        return;
    }

    $size = get_post_meta($post_id, 'attribute_pa_velkost', true);
    $order_by_size = ['a5' => 0, 'a4' => 1];

    if (! isset($order_by_size[$size]) || (int) $post->menu_order === $order_by_size[$size]) {
        return;
    }

    remove_action('save_post_product_variation', 'graceartOrderVariationBySize', 20);
    wp_update_post(['ID' => $post_id, 'menu_order' => $order_by_size[$size]]);
    add_action('save_post_product_variation', 'graceartOrderVariationBySize', 20, 2);

    delete_transient('wc_product_children_' . $post->post_parent);
}
add_action('save_post_product_variation', 'graceartOrderVariationBySize', 20, 2);

add_action('template_redirect', function (): void {
    if (! is_singular('product') || ! is_main_query()) {
        return;
    }

    $post_id = get_queried_object_id();

    if (! $post_id) {
        return;
    }

    $terms = wp_get_object_terms($post_id, 'product_visibility', ['fields' => 'slugs']);
    $terms = is_wp_error($terms) ? [] : $terms;

    $is_hidden = in_array('exclude-from-catalog', $terms, true) && in_array('exclude-from-search', $terms, true);

    if (! $is_hidden) {
        return;
    }

    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    nocache_headers();
}, 5);

add_action('template_redirect', function (): void {
    if (! is_singular('product') || ! is_main_query() || is_admin()) {
        return;
    }

    $post_id = get_queried_object_id();

    if (! $post_id) {
        return;
    }

    $views = (int) get_post_meta($post_id, '_graceart_view_count', true);
    update_post_meta($post_id, '_graceart_view_count', $views + 1);
});

add_filter('manage_edit-product_columns', function (array $columns): array {
    $offset = array_search('price', array_keys($columns), true);

    if ($offset === false) {
        $columns['graceart_total_sales'] = __('Predané', 'graceart');
        $columns['graceart_view_count'] = __('Počet zhliadnutí', 'graceart');

        return $columns;
    }

    return array_slice($columns, 0, $offset + 1, true)
        + [
            'graceart_total_sales' => __('Predané', 'graceart'),
            'graceart_view_count' => __('Počet zhliadnutí', 'graceart'),
        ]
        + array_slice($columns, $offset + 1, null, true);
});

add_action('manage_product_posts_custom_column', function (string $column, int $post_id): void {
    if ($column === 'graceart_total_sales') {
        echo esc_html((string) (int) get_post_meta($post_id, 'total_sales', true));
    }

    if ($column === 'graceart_view_count') {
        echo esc_html((string) (int) get_post_meta($post_id, '_graceart_view_count', true));
    }
}, 10, 2);

add_filter('manage_edit-product_sortable_columns', function (array $columns): array {
    $columns['graceart_total_sales'] = 'graceart_total_sales';
    $columns['graceart_view_count'] = 'graceart_view_count';

    return $columns;
});

add_action('pre_get_posts', function (WP_Query $query): void {
    if (! is_admin() || ! $query->is_main_query()) {
        return;
    }

    $orderby_meta_keys = [
        'graceart_total_sales' => 'total_sales',
        'graceart_view_count' => '_graceart_view_count',
    ];

    $orderby = $query->get('orderby');

    if (! isset($orderby_meta_keys[$orderby])) {
        return;
    }

    $query->set('meta_key', $orderby_meta_keys[$orderby]);
    $query->set('orderby', 'meta_value_num');
});

function graceartProductVisibilityState(int $post_id): array
{
    $status = get_post_status($post_id);

    if ($status !== 'publish') {
        $labels = [
            'draft' => __('Koncept', 'graceart'),
            'pending' => __('Čaká na kontrolu', 'graceart'),
            'private' => __('Súkromné', 'graceart'),
            'trash' => __('V koši', 'graceart'),
            'future' => __('Naplánované', 'graceart'),
        ];

        return ['visible' => false, 'label' => $labels[$status] ?? __('Skryté', 'graceart')];
    }

    $terms = wp_get_object_terms($post_id, 'product_visibility', ['fields' => 'slugs']);
    $terms = is_wp_error($terms) ? [] : $terms;

    $hidden_from_catalog = in_array('exclude-from-catalog', $terms, true);
    $hidden_from_search = in_array('exclude-from-search', $terms, true);

    if ($hidden_from_catalog && $hidden_from_search) {
        return ['visible' => false, 'label' => __('Skryté', 'graceart')];
    }

    if ($hidden_from_catalog) {
        return ['visible' => false, 'label' => __('Skryté v katalógu', 'graceart')];
    }

    if ($hidden_from_search) {
        return ['visible' => false, 'label' => __('Skryté vo vyhľadávaní', 'graceart')];
    }

    return ['visible' => true, 'label' => __('Viditeľné', 'graceart')];
}

add_filter('manage_edit-product_columns', function (array $columns): array {
    $offset = array_search('name', array_keys($columns), true);

    if ($offset === false) {
        $columns['graceart_visibility'] = __('Viditeľnosť', 'graceart');

        return $columns;
    }

    return array_slice($columns, 0, $offset + 1, true)
        + ['graceart_visibility' => __('Viditeľnosť', 'graceart')]
        + array_slice($columns, $offset + 1, null, true);
});

add_action('manage_product_posts_custom_column', function (string $column, int $post_id): void {
    if ($column !== 'graceart_visibility') {
        return;
    }

    $state = graceartProductVisibilityState($post_id);
    $color = $state['visible'] ? '#2e7d32' : '#c0392b';

    printf(
        '<span style="display:inline-block;padding:2px 10px;border-radius:10px;font-size:12px;font-weight:600;color:#fff;background-color:%s;">%s</span>',
        esc_attr($color),
        esc_html($state['label']),
    );
}, 10, 2);

add_action('admin_head-edit.php', function (): void {
    $screen = get_current_screen();

    if (! $screen instanceof WP_Screen || $screen->post_type !== 'product') {
        return;
    }

    echo '<style>.column-graceart_visibility{width:130px;white-space:normal;}</style>';
});

add_filter('manage_edit-product_columns', function (array $columns): array {
    unset($columns['product_tag'], $columns['taxonomy-product_brand']);

    return $columns;
}, 20);

add_filter('post_row_actions', function (array $actions, WP_Post $post): array {
    if ($post->post_type !== 'product' || $post->post_status !== 'publish' || ! current_user_can('edit_post', $post->ID)) {
        return $actions;
    }

    $terms = wp_get_object_terms($post->ID, 'product_visibility', ['fields' => 'slugs']);
    $terms = is_wp_error($terms) ? [] : $terms;
    $is_hidden = in_array('exclude-from-catalog', $terms, true) && in_array('exclude-from-search', $terms, true);

    $url = wp_nonce_url(
        add_query_arg(['action' => 'graceart_toggle_product_visibility', 'post' => $post->ID], admin_url('admin.php')),
        'graceart_toggle_product_visibility_' . $post->ID,
    );

    $label = $is_hidden ? __('Zrušiť skrytie', 'graceart') : __('Skryť', 'graceart');

    $actions['graceart_toggle_visibility'] = sprintf('<a href="%s">%s</a>', esc_url($url), esc_html($label));

    return $actions;
}, 10, 2);

add_action('admin_action_graceart_toggle_product_visibility', function (): void {
    $post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;

    if (! $post_id || ! current_user_can('edit_post', $post_id)) {
        wp_die(esc_html__('Nemáte oprávnenie na túto akciu.', 'graceart'));
    }

    check_admin_referer('graceart_toggle_product_visibility_' . $post_id);

    $terms = wp_get_object_terms($post_id, 'product_visibility', ['fields' => 'slugs']);
    $terms = is_wp_error($terms) ? [] : $terms;
    $is_hidden = in_array('exclude-from-catalog', $terms, true) && in_array('exclude-from-search', $terms, true);

    if ($is_hidden) {
        wp_remove_object_terms($post_id, ['exclude-from-catalog', 'exclude-from-search'], 'product_visibility');
    } else {
        wp_set_object_terms($post_id, ['exclude-from-catalog', 'exclude-from-search'], 'product_visibility', false);
    }

    wp_safe_redirect(wp_get_referer() ?: admin_url('edit.php?post_type=product'));
    exit;
});

/* Media library upload limit: 6 MB. This only lifts WordPress's own cap; PHP
   (upload_max_filesize, post_max_size) and nginx (client_max_body_size) must
   allow at least as much, or larger files are still rejected there. */
add_filter('upload_size_limit', static function (): int {
    return 6 * MB_IN_BYTES;
});
