<?php
/**
 * Helper functions for the GraceArt PDF invoice template.
 *
 * Loaded automatically by PDF Invoices & Packing Slips when this template is selected.
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! function_exists( 'graceart_wcpdf_shop_address_lines' ) ) :
/**
 * Supplier ("Dodávateľ") address, one entry per line.
 *
 * Read from the WooCommerce store address (WooCommerce → Settings → General),
 * with the postcode and city combined onto one line. Falls back to the PDF
 * plugin's own shop address fields, then to its legacy address textarea.
 *
 * @param object $document
 * @return array
 */
function graceart_wcpdf_shop_address_lines( $document ) {
	$lines = function_exists( 'graceartCompanyAddressLines' ) ? graceartCompanyAddressLines() : array();

	if ( empty( $lines ) ) {
		// The get_shop_address_*() getters run the value through wpautop(), so read the
		// raw setting text instead — same as the plugin does when it builds an address.
		$field = function( $key ) use ( $document ) {
			return trim( wp_strip_all_tags( $document->get_settings_text( $key, '', false ) ) );
		};

		$city_line = trim( sprintf(
			'%s %s',
			$field( 'shop_address_postcode' ),
			$field( 'shop_address_city' )
		) );

		$lines = array(
			$field( 'shop_address_line_1' ),
			$field( 'shop_address_line_2' ),
			$city_line,
			trim( wp_strip_all_tags( $document->get_shop_address_country() ) ),
		);
	}

	$lines = array_values( array_filter( array_map( 'trim', $lines ) ) );

	// Legacy setups only have the single address textarea filled in.
	if ( empty( $lines ) ) {
		$address = html_entity_decode( $document->get_shop_address(), ENT_QUOTES, 'UTF-8' );
		$address = preg_replace( '#<br\s*/?>#i', "\n", $address );
		$lines   = array_values( array_filter( array_map( 'trim', explode( "\n", wp_strip_all_tags( $address ) ) ) ) );
	}

	return apply_filters( 'graceart_wcpdf_shop_address_lines', $lines, $document );
}
endif;

if ( ! function_exists( 'graceart_wcpdf_variable_symbol' ) ) :
/**
 * Variable symbol ("Variabilný symbol") — the invoice number without separators,
 * falling back to the order number.
 *
 * @param object $document
 * @return string
 */
function graceart_wcpdf_variable_symbol( $document ) {
	$symbol = '';
	$number = $document->get_number( $document->get_type() );

	if ( ! empty( $number ) && is_callable( array( $number, 'get_formatted' ) ) ) {
		$symbol = preg_replace( '/\D/', '', $number->get_formatted() );
	}

	if ( empty( $symbol ) ) {
		$symbol = preg_replace( '/\D/', '', (string) $document->get_order_number() );
	}

	return apply_filters( 'graceart_wcpdf_variable_symbol', $symbol, $document );
}
endif;

if ( ! function_exists( 'graceart_wcpdf_tax_point_date' ) ) :
/**
 * Date of the taxable supply ("Dátum vzniku daňovej povinnosti").
 * Uses the payment date when the order is paid, otherwise the invoice date.
 *
 * @param object $document
 * @return string
 */
function graceart_wcpdf_tax_point_date( $document ) {
	$date = $document->get_payment_date();

	if ( empty( $date ) ) {
		$date = $document->get_date( $document->get_type(), null, 'view', true );
	}

	return apply_filters( 'graceart_wcpdf_tax_point_date', (string) $date, $document );
}
endif;

if ( ! function_exists( 'graceart_wcpdf_ico' ) ) :
/**
 * IČO (company registration number), from the WooCommerce settings, falling back
 * to the PDF plugin's "COC number" field.
 *
 * @param object $document
 * @return string
 */
function graceart_wcpdf_ico( $document ) {
	$ico = function_exists( 'graceartCompanyIco' ) ? graceartCompanyIco() : '';

	if ( '' === $ico ) {
		$ico = (string) $document->get_shop_coc_number();
	}

	return (string) apply_filters( 'graceart_wcpdf_ico', $ico, $document );
}
endif;

if ( ! function_exists( 'graceart_wcpdf_dic' ) ) :
/**
 * DIČ (tax number), from the WooCommerce settings. Note this is not the VAT ID --
 * having a DIČ does not make the shop a VAT payer.
 *
 * @param object $document
 * @return string
 */
function graceart_wcpdf_dic( $document ) {
	$dic = function_exists( 'graceartCompanyDic' ) ? graceartCompanyDic() : '';

	return (string) apply_filters( 'graceart_wcpdf_dic', $dic, $document );
}
endif;

if ( ! function_exists( 'graceart_wcpdf_vat_id' ) ) :
/**
 * IČ DPH (VAT ID), from the WooCommerce settings, falling back to the PDF
 * plugin's "VAT number" field.
 *
 * @param object $document
 * @return string
 */
function graceart_wcpdf_vat_id( $document ) {
	$vat_id = function_exists( 'graceartCompanyVatId' ) ? graceartCompanyVatId() : '';

	if ( '' === $vat_id ) {
		$vat_id = (string) $document->get_shop_vat_number();
	}

	return (string) apply_filters( 'graceart_wcpdf_vat_id', $vat_id, $document );
}
endif;

if ( ! function_exists( 'graceart_wcpdf_is_vat_registered' ) ) :
/**
 * Whether the shop charges VAT, i.e. whether it has an IČ DPH. Drives the
 * "Dodávateľ nie je platcom DPH." note.
 *
 * @param object $document
 * @return bool
 */
function graceart_wcpdf_is_vat_registered( $document ) {
	$registered = '' !== graceart_wcpdf_vat_id( $document );

	return (bool) apply_filters( 'graceart_wcpdf_is_vat_registered', $registered, $document );
}
endif;

if ( ! function_exists( 'graceart_wcpdf_has_shipping_line' ) ) :
/**
 * Whether a "Poštovné a balné" row should be printed.
 *
 * @param object $document
 * @return bool
 */
function graceart_wcpdf_has_shipping_line( $document ) {
	$order = $document->order;
	$has   = ! empty( $order ) && is_callable( array( $order, 'get_shipping_methods' ) )
		? count( $order->get_shipping_methods() ) > 0
		: false;

	return (bool) apply_filters( 'graceart_wcpdf_has_shipping_line', $has, $document );
}
endif;

/**
 * dompdf sizes a line box as `line-height / font-size * font-height`, where
 * font-height is `(ascender - descender) * font_height_ratio`. With the default
 * ratio of 1.1 that makes every line box 1.498x the line-height this stylesheet
 * asks for (Open Sans has an ascender/descender span of 1.362em).
 *
 * Cancelling the ascender/descender span out of the ratio makes `line-height`
 * behave as CSS specifies, so style.css can use plain, readable values.
 */
const GRACEART_WCPDF_FONT_ASCENDER_DESCENDER_SPAN = 1.3621;

add_filter( 'wpo_wcpdf_dompdf_options', function( $options ) {
	if ( is_array( $options ) ) {
		$options['fontHeightRatio'] = 1 / GRACEART_WCPDF_FONT_ASCENDER_DESCENDER_SPAN;
	}

	return $options;
} );

/**
 * The plugin appends its own due date row for custom templates, assuming the
 * Simple template's table markup. This template prints the due date itself.
 */
add_action( 'wpo_wcpdf_before_document', function() {
	if ( function_exists( 'WPO_WCPDF' ) && ! empty( WPO_WCPDF()->main ) ) {
		remove_action( 'wpo_wcpdf_after_order_data', array( WPO_WCPDF()->main, 'display_due_date_table_row' ), 10 );
	}
}, 1 );

/**
 * Ink saving mode support.
 */
add_filter( 'wpo_ips_ink_saving_supported_templates', function( $templates ) {
	$templates[] = 'theme/GraceArt';
	return $templates;
} );

add_filter( 'wpo_ips_ink_saving_css', function( $css, $document, $current_template ) {
	if ( 'theme/GraceArt' !== $current_template ) {
		return $css;
	}

	$css .= '
		.order-details thead th {
			color: black;
			background-color: white;
		}
	';

	return $css;
}, 10, 3 );
