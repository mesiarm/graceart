<?php
/**
 * Grace Art – funkcie témy.
 *
 * Téma je odvodená od Twenty Twenty-Five. Vzhľad je v theme.json,
 * rozloženie stránok v priečinkoch templates/, parts/ a patterns/.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vlastná kategória vzorov v editore, aby sestra našla bloky Grace Art na jednom mieste.
 */
function graceart_register_pattern_category() {
	register_block_pattern_category(
		'graceart',
		array( 'label' => 'Grace Art' )
	);
}
add_action( 'init', 'graceart_register_pattern_category' );

/**
 * Podpora WooCommerce galérie produktu (zoom, lightbox, posúvanie fotiek).
 */
function graceart_setup() {
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'graceart_setup' );
