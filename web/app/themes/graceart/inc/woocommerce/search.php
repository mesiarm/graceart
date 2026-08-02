<?php

add_action('pre_get_posts', function (WP_Query $query): void {
    if (is_admin() || ! $query->is_main_query() || ! $query->is_search()) {
        return;
    }

    $query->set('post_type', 'product');

    $category = sanitize_title(wp_unslash($_GET['product_cat'] ?? ''));

    if ($category !== '') {
        $query->set('tax_query', [[
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => $category,
        ]]);
    }
});

function graceartProductCategoryOptions(string $selected = ''): string
{
    if (! taxonomy_exists('product_cat')) {
        return '';
    }

    $terms = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => true,
    ]);

    $options = '<option value="">' . esc_html__('Všetky kategórie', 'graceart') . '</option>';

    if (is_wp_error($terms)) {
        return $options;
    }

    foreach ($terms as $term) {
        $options .= sprintf(
            '<option value="%s"%s>%s</option>',
            esc_attr($term->slug),
            selected($selected, $term->slug, false),
            esc_html($term->name),
        );
    }

    return $options;
}
