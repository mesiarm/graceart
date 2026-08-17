<?php

const GRACEART_HOMEPAGE_CATEGORY_SLOTS = 5;

add_action('init', function (): void {
    for ($index = 0; $index < GRACEART_HOMEPAGE_CATEGORY_SLOTS; $index++) {
        register_post_meta('page', '_graceart_home_category_' . ($index + 1), [
            'type' => 'integer',
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'absint',
            'auth_callback' => function (): bool {
                return current_user_can('edit_pages');
            },
        ]);
    }

    register_post_meta('page', '_graceart_home_hero_slides', [
        'type' => 'array',
        'single' => true,
        'default' => [],
        'show_in_rest' => [
            'schema' => [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'image_id' => ['type' => 'integer'],
                        'title' => ['type' => 'string'],
                        'subtitle' => ['type' => 'string'],
                        'button_text' => ['type' => 'string'],
                        'button_url' => ['type' => 'string'],
                    ],
                ],
            ],
        ],
        'auth_callback' => function (): bool {
            return current_user_can('edit_pages');
        },
    ]);

});

function graceartHomepageCategoryBanners(?int $post_id = null): array
{
    $post_id = $post_id ?: (int) get_option('page_on_front');
    $banners = [];

    for ($index = 0; $index < GRACEART_HOMEPAGE_CATEGORY_SLOTS; $index++) {
        $term_id = $post_id ? (int) get_post_meta($post_id, '_graceart_home_category_' . ($index + 1), true) : 0;
        $term = $term_id ? get_term($term_id, 'product_cat') : null;

        if (! ($term instanceof WP_Term) || is_wp_error($term)) {
            continue;
        }

        $thumbnail_id = (int) get_term_meta($term->term_id, 'thumbnail_id', true);
        $image = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'woocommerce_thumbnail') : '';
        $term_link = get_term_link($term);

        $banners[] = [
            'label' => $term->name,
            'count' => $term->count,
            'image' => $image ?: (function_exists('wc_placeholder_img_src') ? wc_placeholder_img_src('woocommerce_thumbnail') : ''),
            'url' => is_wp_error($term_link) ? home_url('/') : $term_link,
        ];
    }

    return $banners;
}

function graceartHomepageHeroSlides(?int $post_id = null): array
{
    $post_id = $post_id ?: (int) get_option('page_on_front');
    $raw = $post_id ? get_post_meta($post_id, '_graceart_home_hero_slides', true) : [];

    if (! is_array($raw) || ! $raw) {
        return graceartDefaultHomepageHeroSlides();
    }

    $slides = array_values(array_filter(array_map(function ($slide): ?array {
        if (! is_array($slide)) {
            return null;
        }

        $image_id = (int) ($slide['image_id'] ?? 0);

        return [
            'image' => $image_id ? (string) wp_get_attachment_image_url($image_id, 'full') : '',
            'title' => (string) ($slide['title'] ?? ''),
            'subtitle' => (string) ($slide['subtitle'] ?? ''),
            'button_text' => (string) ($slide['button_text'] ?? ''),
            'button_url' => (string) ($slide['button_url'] ?? ''),
        ];
    }, $raw)));

    return $slides ?: graceartDefaultHomepageHeroSlides();
}

function graceartDefaultHomepageHeroSlides(): array
{
    return [
        [
            'image' => fullTemplateUri('assets/images/slider/home1/slide-1.webp'),
            'title' => __('Handicraft Shop', 'graceart'),
            'subtitle' => __('Just for you', 'graceart'),
            'button_text' => __('shop now', 'graceart'),
            'button_url' => graceartShopUrl(),
        ],
        [
            'image' => fullTemplateUri('assets/images/slider/home1/slide-2.webp'),
            'title' => __('Newly arrived', 'graceart'),
            'subtitle' => __('Sale up to 10%', 'graceart'),
            'button_text' => __('shop now', 'graceart'),
            'button_url' => graceartShopUrl(),
        ],
        [
            'image' => fullTemplateUri('assets/images/slider/home1/slide-3.webp'),
            'title' => __('Affectious gifts', 'graceart'),
            'subtitle' => __('For friends & family', 'graceart'),
            'button_text' => __('shop now', 'graceart'),
            'button_url' => graceartShopUrl(),
        ],
    ];
}

const GRACEART_HOMEPAGE_BESTSELLER_COUNT = 8;

/**
 * Best selling products for the homepage, generated automatically from total_sales.
 *
 * Only products that have actually sold are returned, so early on the section
 * shows fewer than $limit rather than padding it out with unsold products.
 */
function graceartHomepageBestsellerIds(int $limit = GRACEART_HOMEPAGE_BESTSELLER_COUNT): array
{
    if (! function_exists('wc_get_products')) {
        return [];
    }

    $ids = wc_get_products([
        'status' => 'publish',
        'limit' => $limit,
        'meta_key' => 'total_sales',
        // Date is the tiebreak so the order stays stable while sales counts are equal.
        'orderby' => ['meta_value_num' => 'DESC', 'date' => 'DESC'],
        'return' => 'ids',
    ]);

    // Sorted by sales descending, so anything never sold sits at the tail.
    return array_values(array_filter($ids, function ($id): bool {
        return (int) get_post_meta($id, 'total_sales', true) > 0;
    }));
}

add_action('admin_menu', function () {
    remove_menu_page('wc-admin&path=/payments/overview');
    remove_submenu_page('woocommerce', 'wc-admin&path=/payments/overview');

    global $menu, $submenu;

    $hidden_menu_titles = [
        'payments',
        'platby',
    ];

    foreach ($menu as $position => $item) {
        $title = strtolower(trim(preg_replace('/\s+\d+$/', '', wp_strip_all_tags($item[0] ?? ''))));

        if (in_array($title, $hidden_menu_titles, true)) {
            unset($menu[$position]);
        }
    }

    foreach ($submenu as $parent => $items) {
        foreach ($items as $position => $item) {
            $title = strtolower(trim(preg_replace('/\s+\d+$/', '', wp_strip_all_tags($item[0] ?? ''))));

            if (in_array($title, $hidden_menu_titles, true)) {
                unset($submenu[$parent][$position]);
            }
        }
    }
}, 999);

add_action('enqueue_block_editor_assets', function (): void {
    $screen = get_current_screen();

    if (! $screen || $screen->post_type !== 'page') {
        return;
    }

    if (taxonomy_exists('product_cat')) {
        $terms = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
        ]);

        $categories = [];

        if (! is_wp_error($terms)) {
            foreach ($terms as $term) {
                $categories[] = [
                    'label' => $term->slug === 'uncategorized' ? __('Nezaradené', 'graceart') : $term->name,
                    'value' => (int) $term->term_id,
                ];
            }
        }

        $script_path = get_template_directory() . '/assets/js/admin-homepage-categories.js';
        $script_url = get_template_directory_uri() . '/assets/js/admin-homepage-categories.js';

        wp_enqueue_script(
            'graceart-homepage-categories-editor',
            $script_url,
            ['wp-data'],
            file_exists($script_path) ? (string) filemtime($script_path) : null,
            true,
        );

        wp_localize_script('graceart-homepage-categories-editor', 'graceartHomepageCategories', [
            'frontPageId' => (int) get_option('page_on_front'),
            'slotCount' => GRACEART_HOMEPAGE_CATEGORY_SLOTS,
            'categories' => $categories,
        ]);
    }

    $hero_script_path = get_template_directory() . '/assets/js/admin-homepage-hero.js';
    $hero_script_url = get_template_directory_uri() . '/assets/js/admin-homepage-hero.js';

    wp_enqueue_media();
    wp_enqueue_script(
        'graceart-homepage-hero-editor',
        $hero_script_url,
        ['wp-data'],
        file_exists($hero_script_path) ? (string) filemtime($hero_script_path) : null,
        true,
    );

    wp_localize_script('graceart-homepage-hero-editor', 'graceartHomepageHero', [
        'frontPageId' => (int) get_option('page_on_front'),
    ]);
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
