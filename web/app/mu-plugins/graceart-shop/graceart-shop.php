<?php

/**
 * Plugin Name: Grace Art – obchod
 * Description: Funkcie e-shopu nezávislé od témy: IČO/DIČ/IČ DPH v nastaveniach WooCommerce, doprava zadarmo, košík a pokladňa, faktúra (PDF).
 * Version: 1.0.0
 */

defined('ABSPATH') || exit;

// The classic theme "graceart" ships the same functions; while it is active
// it stays the only source, so the two never collide.
if (get_option('stylesheet') === 'graceart') {
    return;
}

const GRACEART_SHOP_DIR = __DIR__ . '/';

define('GRACEART_SHOP_URL', trailingslashit(WPMU_PLUGIN_URL) . 'graceart-shop/');

foreach (['company', 'shortcodes', 'menus', 'shipping', 'availability', 'catalog', 'wishlist', 'emails', 'cart', 'checkout', 'notices', 'pdf'] as $graceart_shop_module) {
    require_once GRACEART_SHOP_DIR . 'inc/' . $graceart_shop_module . '.php';
}

unset($graceart_shop_module);
