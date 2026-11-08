<?php

/**
 * Comments are switched off everywhere except WooCommerce product reviews.
 *
 * The shop has no blog discussion, so comments on posts, pages and other
 * types stay closed. Product reviews remain available, with spam control:
 *  - only logged-in verified buyers can review (WooCommerce setting + comment_registration),
 *  - every review is held for manual approval (comment_moderation, no "previously approved" shortcut),
 *  - reviews containing links are held (comment_max_links = 0),
 *  - anonymous comments over REST, pings, trackbacks and XML-RPC comments are closed.
 * The matching options are stored in the database (see CHANGELOG / setup notes).
 */
add_filter('comments_open', function ($open, $post_id) {
    return get_post_type($post_id) === 'product';
}, 20, 2);
add_filter('pings_open', '__return_false', 20);
add_filter('pre_option_default_comment_status', fn () => 'closed');
add_filter('pre_option_default_ping_status', fn () => 'closed');
add_filter('rest_allow_anonymous_comments', '__return_false');

add_action('init', function (): void {
    foreach (get_post_types() as $post_type) {
        if ($post_type !== 'product' && post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
}, 100);

add_filter('xmlrpc_methods', function (array $methods): array {
    unset($methods['wp.newComment'], $methods['wp.getComments'], $methods['wp.getComment'], $methods['wp.deleteComment'], $methods['wp.editComment'], $methods['wp.getCommentStatusList'], $methods['wp.getCommentCount']);

    return $methods;
});

add_action('admin_menu', function (): void {
    remove_submenu_page('options-general.php', 'options-discussion.php');
});

add_action('admin_init', function (): void {
    global $pagenow;

    if ($pagenow === 'options-discussion.php') {
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
