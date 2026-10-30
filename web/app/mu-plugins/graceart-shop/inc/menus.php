<?php

/**
 * Header and footer menus as real WordPress menus (Appearance → Editor →
 * Navigation), so they are edited without touching the theme.
 *
 * The theme's navigation blocks carry a class "graceart-menu-<slug>"; this
 * file points such a block at the menu with that slug. A menu that does not
 * exist yet is created once, with the links the site had before; after that
 * it belongs to the editors and is never overwritten. When no menu is found
 * the links written in the template are shown.
 */

const GRACEART_MENUS_VERSION = '1';

/**
 * Starting content of the menus. A page is found by the page option of
 * WooCommerce/WordPress when given, else by its slug; a product category by
 * its slug. A link whose page or category does not exist is left out.
 */
function graceartMenuDefinitions(): array
{
    return [
        'hlavne-menu' => [
            'title' => 'Hlavné menu (hlavička)',
            'items' => [
                ['label' => 'Produkty', 'option' => 'woocommerce_shop_page_id', 'slug' => 'produkty'],
                ['label' => 'O mne', 'slug' => 'o-mne'],
                ['label' => 'Kontakt', 'slug' => 'kontakt'],
            ],
        ],
        'pata-kolekcie' => [
            'title' => 'Pätička – Kolekcie',
            'items' => [
                ['label' => 'Kožené zápisníky', 'term' => 'kozene-zapisniky'],
                ['label' => 'Autorské zápisníky', 'term' => 'autorske-zapisniky'],
                ['label' => 'Kožené fotoalbumy', 'term' => 'knihy'],
                ['label' => 'Všetky produkty', 'option' => 'woocommerce_shop_page_id', 'slug' => 'produkty'],
            ],
        ],
        'pata-nakupovanie' => [
            'title' => 'Pätička – Nakupovanie',
            'items' => [
                ['label' => 'Zoznam prianí', 'option' => 'yith_wcwl_wishlist_page_id', 'slug' => 'zoznam-priani'],
                ['label' => 'Doprava a platba', 'slug' => 'doprava-a-platba'],
                ['label' => 'Vrátenie tovaru', 'option' => 'woocommerce_refund_returns_page_id', 'slug' => 'vratenie-tovaru'],
                ['label' => 'Obchodné podmienky', 'option' => 'woocommerce_terms_page_id', 'slug' => 'obchodne-podmienky'],
                ['label' => 'Ochrana osobných údajov', 'option' => 'wp_page_for_privacy_policy', 'slug' => 'ochrana-sukromia'],
                ['label' => 'Zásady cookies', 'slug' => 'cookies'],
            ],
        ],
    ];
}

function graceartMenuPage(array $item): ?WP_Post
{
    $page = null;

    if (! empty($item['option'])) {
        $page_id = (int) get_option($item['option']);
        $page = $page_id > 0 ? get_post($page_id) : null;
    }

    if (! $page instanceof WP_Post && ! empty($item['slug'])) {
        $page = get_page_by_path($item['slug']);
    }

    return $page instanceof WP_Post && $page->post_status === 'publish' ? $page : null;
}

function graceartMenuContent(array $items): string
{
    $blocks = '';

    foreach ($items as $item) {
        if (! empty($item['term'])) {
            $term = get_term_by('slug', $item['term'], 'product_cat');
            $url = $term instanceof WP_Term ? get_term_link($term) : null;

            if ($url !== null && ! is_wp_error($url)) {
                $blocks .= '<!-- wp:navigation-link ' . wp_json_encode([
                    'label' => $item['label'],
                    'type' => 'product_cat',
                    'id' => $term->term_id,
                    'url' => $url,
                    'kind' => 'taxonomy',
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ' /-->';
            }

            continue;
        }

        $page = graceartMenuPage($item);

        if ($page === null) {
            continue;
        }

        $blocks .= '<!-- wp:navigation-link ' . wp_json_encode([
            'label' => $item['label'],
            'type' => 'page',
            'id' => $page->ID,
            'url' => get_permalink($page),
            'kind' => 'post-type',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ' /-->';
    }

    return $blocks;
}

/**
 * ID of the published menu with this slug, or 0.
 */
function graceartMenuId(string $slug): int
{
    static $ids = [];

    if (! isset($ids[$slug])) {
        $found = get_posts([
            'post_type' => 'wp_navigation',
            'name' => $slug,
            'post_status' => 'publish',
            'numberposts' => 1,
            'fields' => 'ids',
            'suppress_filters' => true,
        ]);

        $ids[$slug] = $found ? (int) $found[0] : 0;
    }

    return $ids[$slug];
}

add_action('init', function (): void {
    if (get_option('graceart_menus_version') === GRACEART_MENUS_VERSION || ! post_type_exists('wp_navigation')) {
        return;
    }

    foreach (graceartMenuDefinitions() as $slug => $menu) {
        if (graceartMenuId($slug) > 0) {
            continue;
        }

        $content = graceartMenuContent($menu['items']);

        if ($content === '') {
            continue;
        }

        wp_insert_post([
            'post_type' => 'wp_navigation',
            'post_status' => 'publish',
            'post_title' => $menu['title'],
            'post_name' => $slug,
            'post_content' => $content,
        ]);
    }

    update_option('graceart_menus_version', GRACEART_MENUS_VERSION);
}, 30);

add_filter('render_block_data', function (array $block): array {
    if (($block['blockName'] ?? '') !== 'core/navigation') {
        return $block;
    }

    if (! preg_match('/graceart-menu-([a-z0-9-]+)/', (string) ($block['attrs']['className'] ?? ''), $match)) {
        return $block;
    }

    $menu_id = graceartMenuId($match[1]);

    if ($menu_id > 0) {
        $block['attrs']['ref'] = $menu_id;
    }

    return $block;
});
