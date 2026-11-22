<?php

/**
 * Scripts the site does not need.
 *
 * jQuery Migrate only keeps very old jQuery code working and is not used by
 * the site or its plugins, and the emoji scripts replace characters every
 * browser renders natively. The YITH wishlist widget ships a React bundle
 * (react, lodash, moment, lapilli-ui) that is only needed on the wishlist page
 * and on the product page (the heart button).
 */
add_action('wp_default_scripts', function (WP_Scripts $scripts): void {
    if (is_admin() || empty($scripts->registered['jquery'])) {
        return;
    }

    $scripts->registered['jquery']->deps = array_diff($scripts->registered['jquery']->deps, ['jquery-migrate']);
});

remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

add_action('wp_enqueue_scripts', function (): void {
    // The heart on the product page needs the bundle too (yith-wcwl-add-to-wishlist depends on lapilli-ui);
    // without it WordPress silently drops the script and the heart does nothing.
    if (is_admin() || is_singular('product') || (function_exists('yith_wcwl_is_wishlist_page') && yith_wcwl_is_wishlist_page())) {
        return;
    }

    foreach (['lapilli-ui-components', 'lapilli-ui-date', 'lapilli-ui-styles'] as $handle) {
        wp_dequeue_script($handle);
        wp_deregister_script($handle);
    }
}, 99);

/**
 * The front page hero image is the largest contentful paint element. Tell the
 * browser to fetch it right away instead of waiting for the render-blocking CSS.
 */
add_action('wp_head', function (): void {
    if (! is_front_page()) {
        return;
    }

    printf(
        "<link rel=\"preload\" as=\"image\" href=\"%s\" type=\"image/webp\" fetchpriority=\"high\">\n",
        esc_url(get_theme_file_uri('assets/images/hero.webp'))
    );
}, 1);

add_filter('render_block_core/cover', function (string $html): string {
    if (! str_contains($html, 'graceart-hero') || str_contains($html, 'fetchpriority=')) {
        return $html;
    }

    // Only the first <img> (the cover background) is touched.
    return preg_replace('/<img /', '<img fetchpriority="high" ', $html, 1) ?? $html;
});
