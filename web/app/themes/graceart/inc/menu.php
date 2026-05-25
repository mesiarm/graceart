<?php

add_action('after_setup_theme', function () {
    $menu_locations = array(
        'header-menu' => __('Horné menu', 'graceart'),
        'footer-menu' => __('Dolné menu', 'graceart'),
    );
    register_nav_menus($menu_locations);
});

add_filter('nav_menu_submenu_css_class', function ($classes) {
    return [];
});