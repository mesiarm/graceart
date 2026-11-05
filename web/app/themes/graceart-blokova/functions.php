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

/**
 * Najlacnejší variant, ktorý sa dá kúpiť (na sklade alebo na objednávku).
 * Vypredané varianty sa nerátajú – podľa rovnakej ceny WooCommerce aj triedi.
 */
function graceart_cheapest_variation( $product ) {
	$prices = $product->get_variation_prices( true );
	foreach ( array_keys( $prices['price'] ) as $variation_id ) {
		$variation = wc_get_product( $variation_id );
		if ( $variation && $variation->is_in_stock() && $variation->is_purchasable() ) {
			return $variation;
		}
	}
	return null;
}

/**
 * Produkty s variantmi (napr. veľkosť A5/A4): vždy len najnižšia cena
 * (napr. "60 €" namiesto "60 € – 120 €") – v zoznamoch aj na stránke produktu.
 * Na stránke produktu je lacnejší variant rovno vybraný a pri zmene variantu
 * sa cena prepíše na presnú cenu variantu (assets/js/quantity.js).
 */
function graceart_variable_price_lowest( $price, $product ) {
	if ( is_admin() ) {
		return $price;
	}
	$variation = graceart_cheapest_variation( $product );
	if ( $variation ) {
		$min     = wc_get_price_to_display( $variation );
		$min_reg = wc_get_price_to_display( $variation, array( 'price' => $variation->get_regular_price() ) );
	} else {
		$min     = $product->get_variation_price( 'min', true );
		$min_reg = $product->get_variation_regular_price( 'min', true );
	}
	if ( '' === $min ) {
		return $price;
	}
	if ( $min_reg > $min ) {
		return wc_format_sale_price( $min_reg, $min ) . $product->get_price_suffix();
	}
	return wc_price( $min ) . $product->get_price_suffix();
}
add_filter( 'woocommerce_variable_price_html', 'graceart_variable_price_lowest', 10, 2 );

/**
 * Stránka produktu s variantmi: ak v produkte nie je nastavený predvolený
 * variant, vyberie sa najlacnejší variant, ktorý sa dá kúpiť.
 */
function graceart_default_cheapest_variation( $defaults, $product ) {
	if ( ! empty( $defaults ) || is_admin() || ! $product->is_type( 'variable' ) ) {
		return $defaults;
	}
	$variation = graceart_cheapest_variation( $product );
	$attributes = $variation ? array_filter( $variation->get_attributes(), 'strlen' ) : array();
	return $attributes ? $attributes : $defaults;
}
add_filter( 'woocommerce_product_get_default_attributes', 'graceart_default_cheapest_variation', 10, 2 );

/**
 * Triedenie podľa ceny: podľa najnižšej ceny (tej, ktorá sa na webe zobrazuje)
 * v oboch smeroch. WooCommerce pri "od najdrahšieho" triedi podľa najvyššej
 * ceny variantu. Produkty bez ceny ("Viac info") sú vždy na konci.
 */
function graceart_price_ordering_args( $args, $orderby, $order ) {
	if ( 'price' !== $orderby || ! function_exists( 'WC' ) || ! WC()->query ) {
		return $args;
	}
	remove_filter( 'posts_clauses', array( WC()->query, 'order_by_price_asc_post_clauses' ) );
	remove_filter( 'posts_clauses', array( WC()->query, 'order_by_price_desc_post_clauses' ) );
	$direction = 'DESC' === $order ? 'DESC' : 'ASC';
	$callback  = function ( $clauses ) use ( $direction, &$callback ) {
		global $wpdb;
		remove_filter( 'posts_clauses', $callback );
		$clauses['join']   .= " LEFT JOIN {$wpdb->wc_product_meta_lookup} graceart_price ON {$wpdb->posts}.ID = graceart_price.product_id ";
		$clauses['orderby'] = " ( graceart_price.min_price IS NULL OR graceart_price.min_price <= 0 ) ASC, graceart_price.min_price {$direction}, {$wpdb->posts}.ID {$direction} ";
		return $clauses;
	};
	add_filter( 'posts_clauses', $callback );
	return $args;
}
add_filter( 'woocommerce_get_catalog_ordering_args', 'graceart_price_ordering_args', 10, 3 );

/**
 * Tlačidlo pri produktoch s variantmi: "Vybrať variant" namiesto
 * "Výber možností". Tlačidlo vedie na stránku produktu, kde zákazník
 * vyberie variant.
 */
function graceart_variable_button_text( $text, $product ) {
	if ( $product && $product->is_type( 'variable' ) && $product->is_purchasable() && $product->is_in_stock() ) {
		return 'Vybrať variant';
	}
	return $text;
}
add_filter( 'woocommerce_product_add_to_cart_text', 'graceart_variable_button_text', 10, 2 );

/**
 * Ceny bez ",00": 65,00 € sa zobrazí ako 65 €. Ceny s centami
 * (napr. 12,50 €) ostanú nezmenené.
 */
add_filter( 'woocommerce_price_trim_zeros', '__return_true' );

/**
 * Pomocná funkcia: produkt zobrazený na aktuálnej stránke produktu.
 */
function graceart_current_product() {
	if ( ! function_exists( 'wc_get_product' ) || ! is_singular( 'product' ) ) {
		return null;
	}
	$product = wc_get_product( get_queried_object_id() );
	return $product ? $product : null;
}

/**
 * [graceart_parametre] – tabuľka "Parametre" na stránke produktu.
 * Údaje sa berú z produktu: Údaje o produkte → Vlastnosti (napr. Rozmer,
 * Počet strán, Papier, Obal, Väzba, Farba) a z hmotnosti a rozmerov.
 * Ak produkt nemá žiadne parametre, sekcia sa nezobrazí.
 */
function graceart_parametre_shortcode() {
	$product = graceart_current_product();
	if ( ! $product || ! function_exists( 'wc_display_product_attributes' ) ) {
		return '';
	}
	ob_start();
	wc_display_product_attributes( $product );
	$table = trim( ob_get_clean() );
	if ( '' === $table ) {
		return '';
	}
	return '<h2 class="wp-block-heading graceart-product-h2">Parametre</h2>' . $table;
}
add_shortcode( 'graceart_parametre', 'graceart_parametre_shortcode' );

/**
 * [graceart_poznamka_koza] – poznámka o koži, len pri kožených produktoch.
 * Produkt je kožený, ak má "kož" (koža, kožený…) v názve alebo vo vlastnostiach
 * (napr. Materiál: koža). Kategória sa neberie do úvahy.
 */
function graceart_poznamka_koza_shortcode() {
	$product = graceart_current_product();
	if ( ! $product ) {
		return '';
	}
	$text = $product->get_name();
	foreach ( $product->get_attributes() as $attribute ) {
		$text .= ' ' . ( $attribute->is_taxonomy()
			? implode( ' ', wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'names' ) ) )
			: implode( ' ', $attribute->get_options() ) );
	}
	if ( false === mb_strpos( mb_strtolower( $text ), 'kož' ) ) {
		return '';
	}
	$note = '<p class="graceart-leather-note">Koža je prírodný materiál. Drobné vrásky, škrabance a zmeny odtieňa patria k jej charakteru a robia každý kus jedinečným.</p>';
	return $note . graceart_starostlivost_koza( false !== mb_strpos( mb_strtolower( $text ), 'nubuk' ) );
}

/**
 * Rozbaľovací odstavec „Ako sa starať o kožu“ pod poznámkou o koži – spoločný text pre všetky
 * kožené produkty (v popisoch produktov sa neopakuje). Nubuk sa ošetruje inak ako hladká koža.
 */
function graceart_starostlivost_koza( $nubuk ) {
	$tips = $nubuk
		? array(
			'Prach a nečistoty jemne vykefujte mäkkou kefkou na semiš alebo nubuk, vždy jedným smerom.',
			'Nubuk nevoskujte ani nemastite – stratil by svoj zamatový povrch.',
			'Na ochranu môžete použiť impregnačný sprej na semiš a nubuk. Najprv ho vyskúšajte na nenápadnom mieste.',
			'Chráňte ho pred vlhkom a dlhodobým priamym slnkom. Ak zmokne, nechajte ho voľne vyschnúť – nie pri radiátore.',
		)
		: array(
			'Povrch občas utrite suchou a mäkkou bavlnenou handričkou.',
			'Drobné škrabance zjemníte, keď ich krúživo pretriete prstom – vosk v koži sa presunie a škrabanec splynie s patinou.',
			'Raz za 6 – 12 mesiacov môžete kožu vyživiť malým množstvom kondicionéru na kožu alebo včelieho vosku. Najprv ho vyskúšajte na nenápadnom mieste.',
			'Chráňte ju pred vlhkom a dlhodobým priamym slnkom. Ak zmokne, nechajte ju voľne vyschnúť – nie pri radiátore.',
		);
	return '<details class="graceart-care"><summary>Ako sa starať o kožu</summary><ul><li>' . implode( '</li><li>', array_map( 'esc_html', $tips ) ) . '</li></ul></details>';
}
add_shortcode( 'graceart_poznamka_koza', 'graceart_poznamka_koza_shortcode' );

/**
 * [graceart_kategorie_prepinac] – prepínanie kategórií nad zoznamom produktov
 * (stránka Produkty a stránky kategórií). Prázdne kategórie a "Uncategorized"
 * sa nezobrazia. Poradie ako na domovskej stránke, ostatné kategórie na konci.
 */
function graceart_kategorie_prepinac_shortcode() {
	if ( ! function_exists( 'wc_get_page_permalink' ) ) {
		return '';
	}
	$order = array( 'kozene-diare-2027', 'kozene-zapisniky', 'knihy', 'autorske-zapisniky' );
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'hide_empty' => true,
			'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
		)
	);
	if ( is_wp_error( $terms ) || ! $terms ) {
		return '';
	}
	usort(
		$terms,
		function ( $a, $b ) use ( $order ) {
			$pa = array_search( $a->slug, $order, true );
			$pb = array_search( $b->slug, $order, true );
			return ( false === $pa ? 99 : $pa ) <=> ( false === $pb ? 99 : $pb );
		}
	);
	$current = is_product_category() ? get_queried_object_id() : 0;
	$links   = array( array( 'Všetko', wc_get_page_permalink( 'shop' ), 0 === $current ) );
	foreach ( $terms as $term ) {
		$links[] = array( $term->name, get_term_link( $term ), $current === $term->term_id );
	}
	$html = '<nav class="graceart-cat-switch" aria-label="Kategórie produktov">';
	foreach ( $links as $link ) {
		$html .= sprintf(
			'<a href="%s"%s>%s</a>',
			esc_url( $link[1] ),
			$link[2] ? ' class="is-active" aria-current="page"' : '',
			esc_html( $link[0] )
		);
	}
	return $html . '</nav>';
}
add_shortcode( 'graceart_kategorie_prepinac', 'graceart_kategorie_prepinac_shortcode' );

/**
 * [graceart_suvisiace] – "Mohlo by sa vám páčiť": 4 podobné produkty.
 */
function graceart_suvisiace_shortcode() {
	$product = graceart_current_product();
	if ( ! $product || ! function_exists( 'woocommerce_related_products' ) ) {
		return '';
	}
	$previous           = isset( $GLOBALS['product'] ) ? $GLOBALS['product'] : null;
	$GLOBALS['product'] = $product;
	ob_start();
	woocommerce_related_products(
		array(
			'posts_per_page' => 4,
			'columns'        => 4,
			'orderby'        => 'rand',
		)
	);
	$html               = ob_get_clean();
	$GLOBALS['product'] = $previous;
	return $html;
}
add_shortcode( 'graceart_suvisiace', 'graceart_suvisiace_shortcode' );

function graceart_related_heading() {
	return 'Mohlo by sa vám páčiť';
}
add_filter( 'woocommerce_product_related_products_heading', 'graceart_related_heading' );

/**
 * Srdiečko (YITH Zoznam prianí) hneď vedľa tlačidla "Pridať do košíka".
 * Pôvodné umiestnenie pod tlačidlom sa na stránke produktu skryje cez CSS.
 */
function graceart_wishlist_next_to_button() {
	if ( ! is_singular( 'product' ) || ! shortcode_exists( 'yith_wcwl_add_to_wishlist' ) ) {
		return;
	}
	global $product;
	$id = $product ? $product->get_id() : get_queried_object_id();
	echo '<div class="graceart-wishlist" title="Pridať do zoznamu prianí">' . do_shortcode( '[yith_wcwl_add_to_wishlist product_id="' . absint( $id ) . '"]' ) . '</div>';
}
add_action( 'woocommerce_after_add_to_cart_button', 'graceart_wishlist_next_to_button', 20 );

/**
 * Pole s počtom kusov ukázať vždy, aj keď je na sklade len 1 kus.
 * WooCommerce ho vtedy inak skryje a pri produktoch by raz bolo, raz nie.
 */
function graceart_quantity_always_visible( $type ) {
	if ( 'hidden' === $type && is_singular( 'product' ) ) {
		return 'number';
	}
	return $type;
}
add_filter( 'woocommerce_quantity_input_type', 'graceart_quantity_always_visible' );

/**
 * Text o sklade nikdy prázdny. Ak WooCommerce nevie, čo napísať
 * (napr. sklad 0, stav "Na objednávku", ale objednávky nepovolené),
 * napíše sa text podľa stavu skladu.
 */
function graceart_availability_fallback( $text, $product ) {
	if ( '' !== trim( (string) $text ) || ! $product ) {
		return $text;
	}
	if ( 'onbackorder' === $product->get_stock_status() ) {
		return 'Na objednávku';
	}
	if ( 'instock' === $product->get_stock_status() && $product->is_type( 'variation' ) ) {
		return 'Na sklade';
	}
	return $text;
}
add_filter( 'woocommerce_get_availability_text', 'graceart_availability_fallback', 10, 2 );

/**
 * Tlačidlá − a + pri množstve na stránke produktu.
 */
function graceart_quantity_script() {
	if ( is_singular( 'product' ) ) {
		wp_enqueue_script( 'graceart-quantity', get_theme_file_uri( 'assets/js/quantity.js' ), array( 'jquery' ),wp_get_theme()->get( 'Version' ), true );
	}
}
add_action( 'wp_enqueue_scripts', 'graceart_quantity_script' );

/**
 * Zoznamy produktov: po triedení a pri ďalšej strane skok rovno na produkty (assets/js/shop.js).
 */
function graceart_shop_script() {
	if ( is_shop() || is_product_taxonomy() || ( is_search() && 'product' === get_query_var( 'post_type' ) ) ) {
		wp_enqueue_script( 'graceart-shop', get_theme_file_uri( 'assets/js/shop.js' ), array(), wp_get_theme()->get( 'Version' ), true );
	}
}
add_action( 'wp_enqueue_scripts', 'graceart_shop_script' );

/**
 * [graceart_socialne_siete] – okrúhle ikonky Facebook a Instagram s popisom pod nimi
 * (pätička a stránka Kontakt). Odkazy sa menia vo WooCommerce → Nastavenia →
 * Všeobecné (Facebook, Instagram); sieť bez odkazu sa nezobrazí.
 */
function graceart_socialne_siete_shortcode() {
	$networks = array(
		array(
			'Facebook',
			trim( (string) get_option( 'graceart_social_facebook', '' ) ),
			'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15.5 4H14a3.5 3.5 0 0 0-3.5 3.5V20M7.5 11h7"></path></svg>',
		),
		array(
			'Instagram',
			trim( (string) get_option( 'graceart_social_instagram', '' ) ),
			'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3.5" y="3.5" width="17" height="17" rx="5"></rect><circle cx="12" cy="12" r="4"></circle><circle cx="17.2" cy="6.8" r="0.9" fill="currentColor" stroke="none"></circle></svg>',
		),
	);
	$networks = array_filter(
		$networks,
		static function ( $network ) {
			return '' !== $network[1];
		}
	);
	if ( ! $networks ) {
		return '';
	}
	$html = '<div class="graceart-social">';
	foreach ( $networks as $network ) {
		$html .= sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer" aria-label="Grace Art na %s"><span class="graceart-social-icon">%s</span><span class="graceart-social-label">%s</span></a>',
			esc_url( $network[1] ),
			esc_attr( 'Facebook' === $network[0] ? 'Facebooku' : 'Instagrame' ),
			$network[2], // Pevne zadaná ikona z tohto súboru.
			esc_html( $network[0] )
		);
	}
	return $html . '</div>';
}
add_shortcode( 'graceart_socialne_siete', 'graceart_socialne_siete_shortcode' );

/**
 * [graceart_doprava_tabulka] – ceny dopravy na stránke Doprava a platba.
 * Berie sa priamo z WooCommerce → Nastavenia → Doprava (zóny a ich spôsoby),
 * takže ceny sú vždy rovnaké ako v pokladni. Vypnuté spôsoby sa nezobrazia.
 */
function graceart_doprava_tabulka_shortcode() {
	if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
		return '';
	}
	$countries = WC()->countries->get_countries();
	$html      = '';
	foreach ( WC_Shipping_Zones::get_zones() as $zone_data ) {
		$zone      = new WC_Shipping_Zone( $zone_data['id'] );
		$locations = $zone->get_zone_locations();
		$name      = $zone->get_zone_name();
		if ( 1 === count( $locations ) && 'country' === $locations[0]->type && isset( $countries[ $locations[0]->code ] ) ) {
			$name = $countries[ $locations[0]->code ];
		}
		$rows = '';
		$free = '';
		foreach ( $zone->get_shipping_methods( true ) as $method ) {
			if ( 'free_shipping' === $method->id ) {
				$min = $method->get_option( 'min_amount' );
				if ( '' !== $min && in_array( $method->get_option( 'requires' ), array( 'min_amount', 'either' ), true ) ) {
					$free = sprintf( '<tr class="is-free"><td>Pri objednávke nad %s</td><td>zadarmo</td></tr>', wc_price( $min ) );
				}
				continue;
			}
			$cost = $method->get_option( 'cost' );
			$rows .= sprintf(
				'<tr><td>%s</td><td>%s</td></tr>',
				esc_html( $method->get_title() ),
				is_numeric( $cost ) ? wc_price( $cost ) : ''
			);
		}
		if ( '' === $rows ) {
			continue;
		}
		$html .= sprintf(
			'<div class="graceart-shipping-zone"><h3>%s</h3><table class="graceart-shipping-table"><tbody>%s%s</tbody></table></div>',
			esc_html( $name ),
			$rows,
			$free
		);
	}
	return '' === $html ? '' : '<div class="graceart-shipping-zones">' . $html . '</div>';
}
add_shortcode( 'graceart_doprava_tabulka', 'graceart_doprava_tabulka_shortcode' );

/**
 * [graceart_platby] – zapnuté spôsoby platby s ich popisom (WooCommerce →
 * Nastavenia → Platby). Po zapnutí platobnej brány (napr. Comgate) sa objaví sama.
 */
function graceart_platby_shortcode() {
	if ( ! function_exists( 'WC' ) ) {
		return '';
	}
	$items = '';
	foreach ( WC()->payment_gateways()->payment_gateways() as $gateway ) {
		if ( 'yes' !== $gateway->enabled ) {
			continue;
		}
		$items .= sprintf(
			'<li><strong>%s</strong><span>%s</span></li>',
			esc_html( wp_strip_all_tags( $gateway->get_title() ) ),
			esc_html( wp_strip_all_tags( $gateway->get_description() ) )
		);
	}
	return '' === $items ? '' : '<ul class="graceart-payments">' . $items . '</ul>';
}
add_shortcode( 'graceart_platby', 'graceart_platby_shortcode' );

/**
 * Údaje o predajcovi na jednom mieste (Kontakt, Vrátenie tovaru).
 * Meno, IČO a adresa sa berú z WooCommerce → Nastavenia → Všeobecné
 * (plugin graceart-shop), takže sa nepíšu do kódu ani do textov.
 */
function graceart_predajca_meno() {
	return function_exists( 'graceartCompanyName' ) ? graceartCompanyName() : get_bloginfo( 'name' );
}

function graceart_predajca_adresa() {
	$street   = trim( (string) get_option( 'woocommerce_store_address', '' ) );
	$postcode = preg_replace( '/^(\d{3})(\d{2})$/', '$1 $2', trim( (string) get_option( 'woocommerce_store_postcode', '' ) ) );
	$city     = trim( (string) get_option( 'woocommerce_store_city', '' ) );
	return array_values( array_filter( array( $street, trim( $postcode . ' ' . $city ) ) ) );
}

/**
 * [graceart_predajca] – meno predajcu (v textoch šablón).
 */
function graceart_predajca_shortcode() {
	return esc_html( graceart_predajca_meno() );
}
add_shortcode( 'graceart_predajca', 'graceart_predajca_shortcode' );

/**
 * Hranica dopravy zadarmo pre krajinu zákazníka (zóna dopravy, do ktorej patrí),
 * inak null. Bez pluginu graceart-shop sa berie prvá zóna s hranicou.
 */
function graceart_free_shipping_min() {
	if ( function_exists( 'graceartFreeShippingMinForCountry' ) ) {
		return graceartFreeShippingMinForCountry();
	}
	if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
		return null;
	}
	foreach ( WC_Shipping_Zones::get_zones() as $zone_data ) {
		$zone = new WC_Shipping_Zone( $zone_data['id'] );
		foreach ( $zone->get_shipping_methods( true ) as $method ) {
			$min = $method->get_option( 'min_amount' );
			if ( 'free_shipping' === $method->id && is_numeric( $min ) && in_array( $method->get_option( 'requires' ), array( 'min_amount', 'either' ), true ) ) {
				return (float) $min;
			}
		}
	}
	return null;
}

/**
 * [graceart_cart_info] – v košíku: koľko chýba do dopravy zadarmo (s pásikom).
 * Skratka je vložená v obsahu stránky Košík. Text sa mení pri zmene košíka
 * (assets/js/cart.js), lebo košík sa prepočítava bez obnovenia stránky.
 */
function graceart_cart_info_shortcode() {
	$min = graceart_free_shipping_min();
	if ( null === $min || ! function_exists( 'WC' ) || ! WC()->cart ) {
		return '';
	}
	wp_enqueue_script( 'graceart-cart', get_theme_file_uri( 'assets/js/cart.js' ), array( 'wp-data' ), wp_get_theme()->get( 'Version' ), true );
	$total = (float) WC()->cart->get_displayed_subtotal() - (float) WC()->cart->get_discount_total();
	return sprintf(
		'<div class="graceart-cart-info" data-min="%s"%s><p class="graceart-cart-info-text">%s</p><div class="graceart-cart-info-bar" aria-hidden="true"><span style="width:%s%%"></span></div><p class="graceart-cart-info-link"><a href="/doprava-a-platba/">Ceny dopravy a spôsoby platby</a></p></div>',
		esc_attr( $min ),
		WC()->cart->is_empty() ? ' hidden' : '',
		$total >= $min
			? 'Máte <strong>dopravu zadarmo</strong>.'
			: sprintf( 'Do dopravy zadarmo vám chýba <strong>%s</strong>.', wp_strip_all_tags( wc_price( $min - $total ) ) ),
		esc_attr( min( 100, round( $total / $min * 100 ) ) )
	);
}
add_shortcode( 'graceart_cart_info', 'graceart_cart_info_shortcode' );

/**
 * Zoznam prianí (YITH): texty, ktoré plugin nemá preložené do slovenčiny.
 */
function graceart_wishlist_texts( $translation, $text, $domain ) {
	if ( 'yith-woocommerce-wishlist' !== $domain ) {
		return $translation;
	}
	$texts = array(
		'Product name'                      => 'Produkt',
		'Unit price'                        => 'Cena',
		'Stock status'                      => 'Dostupnosť',
		'No products added to the wishlist' => 'Zoznam prianí je zatiaľ prázdny. Pridáte doň produkt kliknutím na srdiečko.',
		'In Stock'                          => 'Na sklade',
		'Out of stock'                      => 'Vypredané',
		'Remove this product'               => 'Odstrániť zo zoznamu prianí',
		'Product added!'                    => 'Pridané do zoznamu prianí',
		'Browse wishlist'                   => 'Zobraziť zoznam prianí',
		'The product is already in your wishlist!' => 'Produkt už v zozname prianí máte.',
	);
	return $texts[ $text ] ?? $translation;
}
add_filter( 'gettext', 'graceart_wishlist_texts', 10, 3 );

/**
 * Kontaktný formulár na stránke Kontakt – skratka [graceart_kontakt_formular].
 */
require_once get_theme_file_path( 'inc/kontakt-formular.php' );

/**
 * Pokladňa: jemnejšie chybové hlášky so správnym skloňovaním.
 * WooCommerce skladá hlášku z názvu poľa („Zadajte platné adresa“), preto texty
 * pod poľom názov poľa neopakujú – hláška je priamo pod ním.
 */
function graceart_checkout_js_texts( $translations, $file, $handle, $domain ) {
	if ( 'woocommerce' !== $domain || ! $translations ) {
		return $translations;
	}
	$data = json_decode( $translations, true );
	if ( ! isset( $data['locale_data']['messages'] ) ) {
		return $translations;
	}
	$texts = array(
		'Please enter a valid %s'                       => 'Prosím, vyplňte toto pole.',
		'Please select a %s'                            => 'Prosím, vyberte jednu z možností.',
		'%s is invalid'                                 => 'Prosím, skontrolujte toto pole.',
		'Please enter a valid email address'            => 'Prosím, skontrolujte e-mailovú adresu.',
		'Please enter a valid postcode'                 => 'Prosím, skontrolujte PSČ.',
		'Please enter a city'                           => 'Prosím, doplňte mesto alebo obec.',
		'Please enter a post code'                      => 'Prosím, doplňte PSČ.',
		'Please enter an address'                       => 'Prosím, doplňte ulicu a číslo domu.',
		'Please select a country / region'              => 'Prosím, vyberte krajinu.',
		'Please check this box if you want to proceed.' => 'Ak chcete pokračovať, prosím, označte toto políčko.',
	);
	$changed = false;
	foreach ( $texts as $text => $translation ) {
		if ( isset( $data['locale_data']['messages'][ $text ] ) ) {
			$data['locale_data']['messages'][ $text ] = array( $translation );
			$changed = true;
		}
	}
	return $changed ? wp_json_encode( $data ) : $translations;
}
add_filter( 'load_script_translations', 'graceart_checkout_js_texts', 10, 4 );

/**
 * Hlášky zo servera (po odoslaní objednávky) a texty pri hodnoteniach produktov.
 */
function graceart_woocommerce_texts( $translation, $text, $domain ) {
	if ( 'woocommerce' !== $domain ) {
		return $translation;
	}
	$texts = array(
		'%s is required'                => 'Prosím, vyplňte pole %s.',
		'%s is a required field.'       => 'Prosím, vyplňte pole %s.',
		'First name'                    => 'Meno',
		'First name (optional)'         => 'Meno (voliteľné)',
		'Add a review'                  => 'Napísať hodnotenie',
		'Be the first to review &ldquo;%s&rdquo;' => 'Napísať hodnotenie',
		'There are no reviews yet.'     => 'Tento produkt zatiaľ nikto neohodnotil.',
		'Only logged in customers who have purchased this product may leave a review.' => 'Hodnotenie môžete napísať po kúpe produktu, keď sa prihlásite do svojho účtu.',
		'Only customers who have purchased this product may leave a review.' => 'Hodnotenie môžu napísať zákazníci, ktorí si produkt kúpili.',
		'Your rating'                   => 'Počet hviezdičiek',
		'Your review'                   => 'Vaša skúsenosť s produktom',
		'Please select a rating'        => 'Prosím, vyberte počet hviezdičiek.',
		'verified owner'                => 'overený nákup',
	);
	return $texts[ $text ] ?? $translation;
}
add_filter( 'gettext', 'graceart_woocommerce_texts', 10, 3 );

function graceart_woocommerce_context_texts( $translation, $text, $context, $domain ) {
	if ( 'woocommerce' !== $domain || 'checkout-validation' !== $context ) {
		return $translation;
	}
	$texts = array(
		'Billing %s'  => '%s (fakturačné údaje)',
		'Shipping %s' => '%s (doručenie)',
	);
	return $texts[ $text ] ?? $translation;
}
add_filter( 'gettext_with_context', 'graceart_woocommerce_context_texts', 10, 4 );

/**
 * Hodnotenia: počet hodnotení pod názvom produktu a hviezdičky vo formulári (1 / 2 – 4 / 5 a viac).
 */
function graceart_woocommerce_plural_texts( $translation, $single, $plural, $number, $domain ) {
	if ( 'woocommerce' !== $domain ) {
		return $translation;
	}
	$forms = array(
		'(%s customer review)' => array( '(%s hodnotenie)', '(%s hodnotenia)', '(%s hodnotení)' ),
		'%d of 5 star'         => array( '%d z 5 hviezdičiek', '%d z 5 hviezdičiek', '%d z 5 hviezdičiek' ),
	);
	if ( ! isset( $forms[ $single ] ) ) {
		return $translation;
	}
	$index = 1 === (int) $number ? 0 : ( $number >= 2 && $number <= 4 ? 1 : 2 );
	return $forms[ $single ][ $index ];
}
add_filter( 'ngettext', 'graceart_woocommerce_plural_texts', 10, 5 );

/**
 * Môj účet: odkaz na Zoznam prianí (YITH) v menu účtu, pred Odhlásením.
 */
function graceart_account_wishlist_item( $items ) {
	$logout = isset( $items['customer-logout'] ) ? array( 'customer-logout' => $items['customer-logout'] ) : array();
	unset( $items['customer-logout'] );
	return $items + array( 'graceart-zoznam-priani' => 'Zoznam prianí' ) + $logout;
}
add_filter( 'woocommerce_account_menu_items', 'graceart_account_wishlist_item' );

function graceart_account_wishlist_url( $url, $endpoint ) {
	if ( 'graceart-zoznam-priani' === $endpoint && function_exists( 'YITH_WCWL' ) ) {
		return YITH_WCWL()->get_wishlist_url();
	}
	return $url;
}
add_filter( 'woocommerce_get_endpoint_url', 'graceart_account_wishlist_url', 10, 2 );

/**
 * Zoznamy produktov: jediný H1 je nadpis v hlavičke (vzory produkty-hlavicka,
 * kategoria-hlavicka). Klasický nadpis WooCommerce pod ňou bol len skrytý cez CSS.
 */
add_filter( 'woocommerce_show_page_title', '__return_false' );

/**
 * Bežné vyhľadávanie (bez lupy v hlavičke) presmerovať na vyhľadávanie v produktoch –
 * web nemá blog, výsledky sa inak zobrazovali ako články.
 */
function graceart_search_products_only() {
	if ( is_admin() || ! is_search() || isset( $_GET['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	wp_safe_redirect( add_query_arg( 'post_type', 'product' ) );
	exit;
}
add_action( 'template_redirect', 'graceart_search_products_only' );

/**
 * Omrvinky Yoast na produkte: Domov / Produkty / Kategória / Produkt. Yoast kategóriu
 * berie zo svojich indexov, ktoré sa na stagingu nevytvárajú – ak chýba, doplní sa
 * hlavná kategória Yoast (inak prvá) aj s nadradenými kategóriami pred názov produktu.
 */
function graceart_breadcrumb_kategoria( $links ) {
	if ( ! is_singular( 'product' ) || count( $links ) < 2 ) {
		return $links;
	}
	foreach ( $links as $link ) {
		if ( isset( $link['term_id'] ) || ( isset( $link['taxonomy'] ) && 'product_cat' === $link['taxonomy'] ) ) {
			return $links;
		}
	}
	$terms = get_the_terms( get_the_ID(), 'product_cat' );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return $links;
	}
	$term    = $terms[0];
	$primary = (int) get_post_meta( get_the_ID(), '_yoast_wpseo_primary_product_cat', true );
	foreach ( $terms as $t ) {
		if ( $t->term_id === $primary ) {
			$term = $t;
		}
	}
	$crumbs = array();
	foreach ( array_reverse( get_ancestors( $term->term_id, 'product_cat', 'taxonomy' ) ) as $ancestor_id ) {
		$ancestor = get_term( $ancestor_id, 'product_cat' );
		$crumbs[] = array(
			'url'  => get_term_link( $ancestor ),
			'text' => $ancestor->name,
		);
	}
	$crumbs[] = array(
		'url'  => get_term_link( $term ),
		'text' => $term->name,
	);
	array_splice( $links, count( $links ) - 1, 0, $crumbs );
	return $links;
}
add_filter( 'wpseo_breadcrumb_links', 'graceart_breadcrumb_kategoria' );

/**
 * Rýchlosť: YITH Zoznam prianí načítava na každej stránke React, lodash, moment a ďalšie
 * knižnice (asi 12 skriptov). Srdiečko na pridanie je len na stránke produktu (v zoznamoch
 * je vypnuté) a zoznam na stránke Zoznam prianí – inde sa súbory YITH nenačítajú.
 * Srdiečko v hlavičke je obyčajný odkaz, YITH nepotrebuje.
 */
function graceart_yith_len_kde_treba() {
	if ( is_admin() || is_singular( 'product' ) || ( function_exists( 'yith_wcwl_is_wishlist_page' ) && yith_wcwl_is_wishlist_page() ) ) {
		return;
	}
	foreach ( array( 'yith-wcwl-add-to-wishlist', 'jquery-yith-wcwl', 'jquery-selectBox', 'wc-prettyPhoto' ) as $handle ) {
		wp_dequeue_script( $handle );
	}
	foreach ( array( 'yith-wcwl-main', 'yith-wcwl-add-to-wishlist', 'jquery-selectBox', 'woocommerce_prettyPhoto_css' ) as $handle ) {
		wp_dequeue_style( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'graceart_yith_len_kde_treba', 100 );

/**
 * Krajiny doručenia v pokladni (výber výdajného miesta rieši plugin Packeta).
 */
require_once get_theme_file_path( 'inc/shipping-countries.php' );
