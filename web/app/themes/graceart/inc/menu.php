<?php

add_action('after_setup_theme', function () {
    $menu_locations = [
        'header-menu' => __('Horné menu', 'graceart'),
        'footer-menu' => __('Dolné menu', 'graceart'),
    ];

    register_nav_menus($menu_locations);
});

class Graceart_Menu_Walker extends Walker_Nav_Menu
{
    private array $submenu_classes = [];

    public function start_lvl(&$output, $depth = 0, $args = null): void
    {
        $classes = $this->submenu_classes[$depth] ?? ['sub-menu'];
        $output .= '<ul class="' . esc_attr(implode(' ', array_unique($classes))) . '">';
    }

    public function display_element($element, &$children_elements, $max_depth, $depth, $args, &$output): void
    {
        if (! $element) {
            return;
        }

        $id_field = $this->db_fields['id'];
        $element->has_children = ! empty($children_elements[$element->$id_field]);

        parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
    }

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0): void
    {
        $classes = empty($item->classes) ? [] : (array) $item->classes;

        if (! empty($item->has_children)) {
            $classes[] = 'has-children';
        }

        $submenu_classes = ['sub-menu'];

        if ($depth === 0 && in_array('mega-menu', $classes, true)) {
            $submenu_classes[] = 'mega-menu';
        }

        $this->submenu_classes[$depth] = $submenu_classes;

        $class_attribute = $classes ? ' class="' . esc_attr(implode(' ', array_filter($classes))) . '"' : '';
        $output .= '<li' . $class_attribute . '>';

        $attributes = '';
        $attributes .= ! empty($item->url) ? ' href="' . esc_url($item->url) . '"' : ' href="#"';
        $attributes .= ! empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
        $attributes .= ! empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
        $attributes .= in_array('mega-menu-title', $classes, true) ? ' class="mega-menu-title"' : '';

        $title = apply_filters('the_title', $item->title, $item->ID);
        $title = apply_filters('nav_menu_item_title', $title, $item, $args, $depth);

        $output .= '<a' . $attributes . '><span class="menu-text">' . esc_html($title) . '</span></a>';
    }
}

function graceartHeaderMenu(string $class = 'site-main-menu justify-content-center'): void
{
    wp_nav_menu([
        'theme_location' => 'header-menu',
        'container' => 'nav',
        'container_class' => $class,
        'fallback_cb' => false,
        'items_wrap' => '<ul>%3$s</ul>',
        'walker' => new Graceart_Menu_Walker(),
    ]);
}

function graceartMobileMenu(): void
{
    wp_nav_menu([
        'theme_location' => 'header-menu',
        'container' => false,
        'fallback_cb' => false,
        'items_wrap' => '<ul>%3$s</ul>',
        'walker' => new Graceart_Menu_Walker(),
    ]);
}

function graceartFooterMenu(): void
{
    wp_nav_menu([
        'theme_location' => 'footer-menu',
        'container' => false,
        'fallback_cb' => false,
        'menu_class' => 'widget-menu justify-content-center',
        'depth' => 1,
    ]);
}
