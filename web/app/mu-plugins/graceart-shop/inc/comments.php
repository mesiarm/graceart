<?php

/**
 * Comments are switched off for the whole site.
 *
 * The shop has no blog discussion; product reviews are disabled too.
 * Closing comments and pings, hiding the UI and removing the endpoints
 * keeps spam from ever reaching the database.
 */
add_filter('comments_open', '__return_false', 20);
add_filter('pings_open', '__return_false', 20);
add_filter('comments_array', '__return_empty_array', 20);
add_filter('get_comments_number', '__return_zero', 20);
add_filter('woocommerce_product_tabs', function (array $tabs): array {
    unset($tabs['reviews']);

    return $tabs;
}, 99);
add_filter('pre_option_woocommerce_enable_reviews', fn () => 'no');
add_filter('pre_option_default_comment_status', fn () => 'closed');
add_filter('pre_option_default_ping_status', fn () => 'closed');

add_action('init', function (): void {
    foreach (get_post_types() as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
}, 100);

add_filter('rest_endpoints', function (array $endpoints): array {
    unset($endpoints['/wp/v2/comments'], $endpoints['/wp/v2/comments/(?P<id>[\d]+)']);

    return $endpoints;
});

add_filter('xmlrpc_methods', function (array $methods): array {
    unset($methods['wp.newComment'], $methods['wp.getComments'], $methods['wp.getComment'], $methods['wp.deleteComment'], $methods['wp.editComment'], $methods['wp.getCommentStatusList'], $methods['wp.getCommentCount']);

    return $methods;
});

add_action('admin_menu', function (): void {
    remove_menu_page('edit-comments.php');
    remove_submenu_page('options-general.php', 'options-discussion.php');
});

add_action('admin_bar_menu', function (WP_Admin_Bar $bar): void {
    $bar->remove_node('comments');
}, 999);

add_action('wp_dashboard_setup', function (): void {
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
});

add_action('admin_init', function (): void {
    global $pagenow;

    if (in_array($pagenow, ['edit-comments.php', 'options-discussion.php'], true)) {
        wp_safe_redirect(admin_url());
        exit;
    }
});

add_action('template_redirect', function (): void {
    if (is_comment_feed()) {
        wp_safe_redirect(home_url('/'), 301);
        exit;
    }
});

remove_action('wp_head', 'feed_links_extra', 3);
