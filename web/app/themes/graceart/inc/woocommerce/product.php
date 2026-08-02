<?php

add_filter('woocommerce_product_single_add_to_cart_text', fn(): string => __('Pridať do košíka', 'graceart'));

add_filter('woocommerce_product_add_to_cart_text', function (string $text, WC_Product $product): string {
    if ($product->is_type('variable')) {
        return __('Vybrať možnosti', 'graceart');
    }

    if ($product->is_type('grouped')) {
        return __('Zobraziť produkty', 'graceart');
    }

    if (! $product->is_in_stock()) {
        return __('Nie je skladom', 'graceart');
    }

    if (! $product->is_purchasable()) {
        return __('Zobraziť produkt', 'graceart');
    }

    return __('Pridať do košíka', 'graceart');
}, 10, 2);

add_filter('woocommerce_product_tabs', function (array $tabs): array {
    if (isset($tabs['description'])) {
        $tabs['description']['title'] = __('Popis', 'graceart');
    }

    if (isset($tabs['reviews'])) {
        global $product;

        $review_count = $product instanceof WC_Product ? $product->get_review_count() : 0;
        $tabs['reviews']['title'] = sprintf(__('Recenzie (%d)', 'graceart'), $review_count);
    }

    return $tabs;
}, 20);

add_filter('woocommerce_product_description_heading', fn(): string => __('Popis', 'graceart'));

add_filter('woocommerce_product_related_products_heading', fn(): string => __('Mohlo by sa vám páčiť', 'graceart'));

add_filter('woocommerce_reviews_title', function (string $title, int $count, WC_Product $product): string {
    if ($count > 0) {
        return sprintf(__('Recenzie (%d) pre %s', 'graceart'), $count, '<span>' . esc_html($product->get_name()) . '</span>');
    }

    return __('Recenzie', 'graceart');
}, 10, 3);

function graceartWishlistProductUrl(WC_Product $product): string
{
    if (function_exists('YITH_WCWL')) {
        return wp_nonce_url(
            add_query_arg('add_to_wishlist', $product->get_id(), $product->get_permalink()),
            'add_to_wishlist',
        );
    }

    return graceartWishlistUrl();
}

function graceartWishlistButton(WC_Product $product): string
{
    if (function_exists('YITH_WCWL')) {
        return '<span class="graceart-wishlist-button">' . do_shortcode('[yith_wcwl_add_to_wishlist]') . '</span>';
    }

    return sprintf(
        '<a href="%1$s" class="btn btn-icon btn-outline-body btn-hover-dark hintT-top" data-hint="%2$s" aria-label="%2$s"><i class="far fa-heart"></i></a>',
        esc_url(graceartWishlistProductUrl($product)),
        esc_attr__('Pridať do zoznamu prianí', 'graceart'),
    );
}

function graceartProductGalleryImages(WC_Product $product): array
{
    $image_ids = array_filter(array_merge(
        [$product->get_image_id()],
        $product->get_gallery_image_ids(),
    ));

    if (! $image_ids) {
        return [[
            'type' => 'image',
            'alt' => $product->get_name(),
            'thumb' => wc_placeholder_img_src('woocommerce_thumbnail'),
            'full' => wc_placeholder_img_src('woocommerce_single'),
            'large' => wc_placeholder_img_src('woocommerce_single'),
            'width' => 700,
            'height' => 1100,
        ]];
    }

    return array_map(function (int $image_id) use ($product) {
        if (wp_attachment_is('video', $image_id)) {
            return [
                'type' => 'video',
                'alt' => get_the_title($image_id) ?: $product->get_name(),
                'video_url' => wp_get_attachment_url($image_id),
                'mime' => get_post_mime_type($image_id) ?: 'video/mp4',
            ];
        }

        $full = wp_get_attachment_image_src($image_id, 'full');

        return [
            'type' => 'image',
            'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: $product->get_name(),
            'thumb' => wp_get_attachment_image_url($image_id, 'woocommerce_thumbnail'),
            'full' => $full[0] ?? wp_get_attachment_image_url($image_id, 'full'),
            'large' => wp_get_attachment_image_url($image_id, 'woocommerce_single'),
            'width' => $full[1] ?? 700,
            'height' => $full[2] ?? 1100,
        ];
    }, $image_ids);
}

function graceartProductGalleryPopupImages(array $images): string
{
    $images = array_values(array_filter($images, fn(array $image) => $image['type'] === 'image'));

    return wp_json_encode(array_map(fn(array $image) => [
        'src' => $image['full'],
        'w' => $image['width'],
        'h' => $image['height'],
    ], $images));
}

function graceartProductLoopClasses(WC_Product $product): string
{
    $classes = ['grid-item', 'col'];

    foreach (wc_get_product_term_ids($product->get_id(), 'product_cat') as $term_id) {
        $classes[] = 'cat-' . $term_id;
    }

    return implode(' ', $classes);
}

function graceartProductLoopCategoryFilters(): array
{
    if (! taxonomy_exists('product_cat')) {
        return [];
    }

    $terms = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => true,
    ]);

    if (is_wp_error($terms)) {
        return [];
    }

    return array_map(function (WP_Term $term): array {
        return [
            'label' => $term->slug === 'uncategorized' ? __('Nezaradené', 'graceart') : $term->name,
            'filter' => '.cat-' . $term->term_id,
        ];
    }, $terms);
}

function graceartProductBadgeHtml(WC_Product $product): string
{
    $badges = [];

    if (! $product->is_in_stock()) {
        $badges[] = '<span class="outofstock"><i class="far fa-frown"></i></span>';
    }

    if ($product->is_featured()) {
        $badges[] = '<span class="hot">' . esc_html__('top', 'graceart') . '</span>';
    }

    if ($product->is_on_sale()) {
        $badges[] = '<span class="onsale">' . esc_html__('zľava', 'graceart') . '</span>';
    }

    return $badges ? '<span class="product-badges">' . implode('', $badges) . '</span>' : '';
}

function graceartProductImageUrl(WC_Product $product, string $size = 'woocommerce_thumbnail'): string
{
    $image_id = $product->get_image_id();

    return $image_id ? wp_get_attachment_image_url($image_id, $size) : wc_placeholder_img_src($size);
}

function graceartProductHoverImageUrl(WC_Product $product, string $size = 'woocommerce_thumbnail'): string
{
    $gallery_ids = $product->get_gallery_image_ids();

    return $gallery_ids ? wp_get_attachment_image_url((int) $gallery_ids[0], $size) : '';
}
