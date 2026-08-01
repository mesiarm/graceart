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
                    'label' => $term->name,
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
