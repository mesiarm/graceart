<?php

/**
 * Scripts the site does not need.
 *
 * jQuery Migrate only keeps very old jQuery code working and is not used by
 * the site or its plugins, and the emoji scripts replace characters every
 * browser renders natively. The YITH wishlist widget ships a React bundle
 * (react, lodash, moment, lapilli-ui) that is only needed on the wishlist page.
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
    if (is_admin() || (function_exists('yith_wcwl_is_wishlist_page') && yith_wcwl_is_wishlist_page())) {
        return;
    }

    foreach (['lapilli-ui-components', 'lapilli-ui-date', 'lapilli-ui-styles'] as $handle) {
        wp_dequeue_script($handle);
        wp_deregister_script($handle);
    }
}, 99);
