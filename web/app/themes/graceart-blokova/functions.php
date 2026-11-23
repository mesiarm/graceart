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
 * Podpora WooCommerce galérie produktu (lightbox, posúvanie fotiek).
 * Zoom pri prejdení myšou je vypnutý (10. 11. 2026, požiadavka developera) – fotka sa zväčší po kliknutí v lightboxe.
 */
function graceart_setup() {
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'graceart_setup' );
// Blok galérie WooCommerce si zoom pri vykreslení zapína sám (filter s prioritou 10) – vypnúť neskorším filtrom
// a skript zoomu vôbec nenačítať (lightbox po kliknutí ostáva).
add_filter( 'woocommerce_single_product_zoom_enabled', '__return_false', 99 );
function graceart_bez_zoomu() {
	wp_dequeue_script( 'wc-zoom' );
	wp_dequeue_script( 'zoom' );
}
add_action( 'wp_footer', 'graceart_bez_zoomu', 1 );

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
	if ( is_search() ) {
		// Vo výsledkoch vyhľadávania pilulky len zúžia hľadanie na kategóriu (hľadané slovo ostáva).
		$base  = add_query_arg( array( 's' => get_search_query( false ), 'post_type' => 'product' ), home_url( '/' ) );
		$slug  = (string) get_query_var( 'product_cat' );
		$links = array( array( 'Všetko', $base, '' === $slug ) );
		foreach ( $terms as $term ) {
			$links[] = array( $term->name, add_query_arg( 'product_cat', $term->slug, $base ), $slug === $term->slug );
		}
	} else {
		$current = is_product_category() ? get_queried_object_id() : 0;
		$links   = array( array( 'Všetko', wc_get_page_permalink( 'shop' ), 0 === $current ) );
		foreach ( $terms as $term ) {
			$links[] = array( $term->name, get_term_link( $term ), $current === $term->term_id );
		}
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
 * Okienko „Pridané do zoznamu prianí“ (rovnaké ako „Pridané do košíka“). Je v pätičke stránky
 * produktu zatvorené; assets/js/pridane.js ho otvorí po udalosti added_to_wishlist od YITH.
 */
function graceart_wishlist_okienko() {
	if ( ! is_singular( 'product' ) || ! function_exists( 'YITH_WCWL' ) ) {
		return;
	}
	$product = wc_get_product( get_queried_object_id() );
	if ( ! $product ) {
		return;
	}
	?>
	<dialog class="graceart-pridane graceart-pridane-priania" aria-labelledby="graceart-priania-nadpis">
		<div class="graceart-pridane-obsah">
			<div class="graceart-pridane-hlava">
				<p id="graceart-priania-nadpis" class="graceart-pridane-nadpis"><span class="graceart-pridane-fajka" aria-hidden="true"></span>Pridané do zoznamu prianí</p>
				<button type="button" class="graceart-pridane-zavriet" aria-label="Zavrieť">&times;</button>
			</div>
			<div class="graceart-pridane-produkt">
				<?php echo $product->get_image( 'woocommerce_thumbnail', array( 'alt' => '', 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<div>
					<p class="graceart-pridane-meno"><?php echo esc_html( $product->get_title() ); ?></p>
					<p class="graceart-pridane-cena"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
				</div>
			</div>
			<div class="graceart-pridane-tlacidla">
				<button type="button" class="graceart-pridane-pokracovat">Pokračovať v nákupe</button>
				<a class="graceart-pridane-do-kosika" href="<?php echo esc_url( YITH_WCWL()->get_wishlist_url() ); ?>">Zobraziť zoznam prianí</a>
			</div>
		</div>
	</dialog>
	<?php
}
add_action( 'wp_footer', 'graceart_wishlist_okienko' );

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
 * Dostupnosť pre zákazníka – pri produkte, variante aj v košíku hneď vidieť
 * (Veronika 31. 10. 2026): „Skladom – odoslanie do 2 – 3 pracovných dní“ alebo
 * „Na objednávku – odoslanie do 1 týždňa“. Lehotu na objednávku a počet kusov
 * na objednávku nastavuje plugin graceart-shop (pri produkte / variante).
 */
function graceart_odoslanie_skladom() {
	return 'do 2 – 3 pracovných dní';
}

function graceart_dostupnost_text( $product, $qty = 1 ) {
	if ( function_exists( 'graceartQuantityIsOnBackorder' ) && graceartQuantityIsOnBackorder( $product, max( 1, (int) $qty ) ) ) {
		return 'Na objednávku – odoslanie ' . graceartLeadTimePhrase( $product );
	}
	if ( ! $product->is_in_stock() ) {
		return 'Vypredané';
	}
	return 'Skladom – odoslanie ' . graceart_odoslanie_skladom();
}

function graceart_availability_text( $text, $product ) {
	if ( ! $product || $product->is_type( 'variable' ) || ( is_admin() && ! wp_doing_ajax() ) ) {
		return $text;
	}
	return graceart_dostupnost_text( $product );
}
add_filter( 'woocommerce_get_availability_text', 'graceart_availability_text', 20, 2 );

/**
 * Blok skladu na stránke produktu: pri malom sklade by WooCommerce napísal
 * „Na sklade zostávajú 2 ks“ – počet kusov zákazníkovi neukazujeme, len dostupnosť.
 */
function graceart_stock_indicator_text( $html, $parsed_block, $instance ) {
	$product = wc_get_product( $instance->context['postId'] ?? get_the_ID() );
	if ( ! $product || '' === trim( $html ) ) {
		return $html;
	}
	$text = graceart_dostupnost_html( graceart_dostupnost_text( $product ) );
	// data-wp-text by text po načítaní prepísal ako jeden riadok – text dávame priamo (varianty prepisuje quantity.js)
	$html = preg_replace( '#\sdata-wp-text="[^"]*"#', '', $html, 1 );
	return preg_replace_callback( '#(<div\b[^>]*>)[^<]*(</div>\s*)$#', function ( $m ) use ( $text ) {
		return $m[1] . $text . $m[2];
	}, $html, 1 );
}

/**
 * „Skladom – odoslanie do 2 – 3 pracovných dní“ → dva riadky:
 * hore stav (Skladom / Na objednávku), pod ním kedy sa odosiela.
 */
function graceart_dostupnost_html( $text ) {
	$parts = explode( ' – ', $text, 2 );
	if ( 2 !== count( $parts ) ) {
		return esc_html( $text );
	}
	return '<span class="graceart-stock-state">' . esc_html( $parts[0] ) . '</span>'
		. '<span class="graceart-stock-note">' . esc_html( $parts[1] ) . '</span>';
}
add_filter( 'render_block_woocommerce/product-stock-indicator', 'graceart_stock_indicator_text', 10, 3 );

/**
 * Košík a pokladňa: pod názvom produktu riadok „Dostupnosť: …“ (podľa počtu kusov –
 * keď sklad nestačí, celá položka je na objednávku).
 */
function graceart_cart_dostupnost( $item_data, $cart_item ) {
	if ( isset( $cart_item['data'] ) && $cart_item['data'] instanceof WC_Product ) {
		$item_data[] = array(
			'key'   => 'Dostupnosť',
			'value' => graceart_dostupnost_text( $cart_item['data'], $cart_item['quantity'] ?? 1 ),
		);
	}
	return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'graceart_cart_dostupnost', 20, 2 );

/**
 * Okienko „Pridané do košíka“ s tlačidlami Pokračovať v nákupe / Prejsť do košíka
 * (namiesto presmerovania do košíka – WooCommerce → Nastavenia → Produkty, vypnuté 31. 10. 2026).
 * Stránka produktu: po pridaní späť na produkt a okienko sa vypíše v pätičke.
 * Zoznamy (AJAX): okienko príde ako fragment do .graceart-pridane-miesto.
 * Otvára ho assets/js/pridane.js.
 */
function graceart_pridane_strana() {
	return is_product() || is_shop() || is_product_taxonomy() || is_front_page() || is_search() || is_page( 'zoznam-priani' );
}

function graceart_pridane_zapamatat( $cart_item_key, $product_id, $quantity ) {
	$pridane = array( $cart_item_key, (float) $quantity );
	if ( wp_doing_ajax() ) {
		$GLOBALS['graceart_pridane_ajax'] = $pridane;
	} elseif ( isset( $_REQUEST['add-to-cart'] ) && WC()->session ) { // phpcs:ignore WordPress.Security.NonceVerification
		WC()->session->set( 'graceart_pridane', $pridane );
	}
}
add_action( 'woocommerce_add_to_cart', 'graceart_pridane_zapamatat', 10, 3 );

function graceart_pridane_presmerovanie( $url ) {
	if ( $url || ! WC()->session || ! WC()->session->get( 'graceart_pridane' ) ) {
		return $url;
	}
	// späť na produkt (GET), aby obnovenie stránky nepridalo produkt znova
	return wp_validate_redirect( wp_get_raw_referer(), $url );
}
add_filter( 'woocommerce_add_to_cart_redirect', 'graceart_pridane_presmerovanie', 20 );

/** Hlášku „… bol pridaný do košíka. Zobraziť košík“ nahrádza okienko. */
function graceart_pridane_bez_hlasky() {
	if ( ! function_exists( 'WC' ) || ! WC()->session || ! WC()->session->get( 'graceart_pridane' ) ) {
		return;
	}
	$notices = WC()->session->get( 'wc_notices', array() );
	if ( empty( $notices['success'] ) ) {
		return;
	}
	$notices['success'] = array_values( array_filter( $notices['success'], function ( $n ) {
		return false === strpos( is_array( $n ) ? $n['notice'] : (string) $n, 'wc-forward' );
	} ) );
	if ( ! $notices['success'] ) {
		unset( $notices['success'] );
	}
	WC()->session->set( 'wc_notices', $notices );
}
add_action( 'template_redirect', 'graceart_pridane_bez_hlasky' );

function graceart_pridane_html( $pridane, $otvorit ) {
	list( $cart_item_key, $pocet ) = array_pad( (array) $pridane, 2, 0 );
	$item = WC()->cart->get_cart_item( $cart_item_key );
	if ( ! $item || empty( $item['data'] ) ) {
		return '';
	}
	$pocet = $pocet > 0 ? $pocet : $item['quantity'];
	$product  = $item['data'];
	$detaily  = array_filter( array_map( 'trim', explode( "\n", wc_get_formatted_cart_item_data( $item, true ) ) ) );
	$kosik    = WC()->cart;
	$spolu    = (float) $kosik->get_displayed_subtotal() - (float) $kosik->get_discount_total();
	$min      = graceart_free_shipping_min();
	$doprava  = '';
	if ( null !== $min ) {
		$doprava = $spolu >= $min
			? ' Máte <strong>dopravu zadarmo</strong>.'
			: sprintf( ' Do dopravy zadarmo chýba <strong>%s</strong>.', wp_strip_all_tags( wc_price( $min - $spolu ) ) );
	}
	ob_start();
	?>
	<dialog class="graceart-pridane" aria-labelledby="graceart-pridane-nadpis"<?php echo $otvorit ? ' open data-otvorit' : ''; ?>>
		<div class="graceart-pridane-obsah">
			<div class="graceart-pridane-hlava">
				<p id="graceart-pridane-nadpis" class="graceart-pridane-nadpis"><span class="graceart-pridane-fajka" aria-hidden="true"></span>Pridané do košíka</p>
				<button type="button" class="graceart-pridane-zavriet" aria-label="Zavrieť">&times;</button>
			</div>
			<div class="graceart-pridane-produkt">
				<?php echo $product->get_image( 'woocommerce_thumbnail', array( 'alt' => '', 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<div>
					<p class="graceart-pridane-meno"><?php echo esc_html( $product->get_title() ); ?></p>
					<?php foreach ( $detaily as $riadok ) : ?>
						<p class="graceart-pridane-detail"><?php echo esc_html( $riadok ); ?></p>
					<?php endforeach; ?>
					<p class="graceart-pridane-cena"><?php echo esc_html( wc_stock_amount( $pocet ) ); ?> ks · <?php echo wp_kses_post( $kosik->get_product_subtotal( $product, $pocet ) ); ?></p>
				</div>
			</div>
			<p class="graceart-pridane-kosik">V košíku: <?php echo esc_html( $kosik->get_cart_contents_count() ); ?> ks za <strong><?php echo wp_strip_all_tags( wc_price( $spolu ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></strong>.<?php echo wp_kses_post( $doprava ); ?></p>
			<div class="graceart-pridane-tlacidla">
				<button type="button" class="graceart-pridane-pokracovat">Pokračovať v nákupe</button>
				<a class="graceart-pridane-do-kosika" href="<?php echo esc_url( wc_get_cart_url() ); ?>">Prejsť do košíka</a>
			</div>
		</div>
	</dialog>
	<?php
	return ob_get_clean();
}

function graceart_pridane_okienko() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart || ! graceart_pridane_strana() ) {
		return;
	}
	$html = '';
	$pridane = WC()->session ? WC()->session->get( 'graceart_pridane' ) : '';
	if ( $pridane ) {
		WC()->session->set( 'graceart_pridane', null );
		$html = graceart_pridane_html( $pridane, true );
	}
	echo '<div class="graceart-pridane-miesto">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
}
add_action( 'wp_footer', 'graceart_pridane_okienko' );

function graceart_pridane_fragment( $fragments ) {
	$pridane = $GLOBALS['graceart_pridane_ajax'] ?? '';
	$fragments['.graceart-pridane-miesto'] = '<div class="graceart-pridane-miesto">' . ( $pridane ? graceart_pridane_html( $pridane, false ) : '' ) . '</div>';
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'graceart_pridane_fragment' );

function graceart_pridane_script() {
	if ( graceart_pridane_strana() ) {
		wp_enqueue_script( 'graceart-pridane', get_theme_file_uri( 'assets/js/pridane.js' ), array( 'jquery' ), wp_get_theme()->get( 'Version' ), true );
	}
}
add_action( 'wp_enqueue_scripts', 'graceart_pridane_script' );

/**
 * Tlačidlá do košíka / pokladne ukážu po kliknutí načítavanie (assets/js/prechod.js).
 */
function graceart_prechod_script() {
	wp_enqueue_script( 'graceart-prechod', get_theme_file_uri( 'assets/js/prechod.js' ), array(), wp_get_theme()->get( 'Version' ), true );
}
add_action( 'wp_enqueue_scripts', 'graceart_prechod_script' );

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
		'Product successfully removed.'     => 'Produkt bol odstránený zo zoznamu prianí.',
		'Product added to cart successfully' => 'Produkt bol pridaný do košíka.',
		'We are sorry, but this feature is available only if cookies on your browser are enabled.' => 'Ľutujeme, táto funkcia vyžaduje povolené cookies vo vašom prehliadači.',
		'View &rsaquo;'                     => 'Zobraziť &rsaquo;',
		'or'                                => 'alebo',
		'Close'                             => 'Zavrieť',
		'Add to cart'                       => 'Pridať do košíka',
		'Add all to cart'                   => 'Pridať všetko do košíka',
		'Quantity'                          => 'Množstvo',
		'Quantity:'                         => 'Množstvo:',
		'Price:'                            => 'Cena:',
		'Stock:'                            => 'Dostupnosť:',
		'Added on:'                         => 'Pridané:',
		'Added on: %s'                      => 'Pridané: %s',
		'Remove'                            => 'Odstrániť',
		'Remove from wishlist'              => 'Odstrániť zo zoznamu prianí',
		'Update'                            => 'Aktualizovať',
		'Apply'                             => 'Použiť',
		'Move'                              => 'Presunúť',
		'Edit title'                        => 'Upraviť názov',
		'Email'                             => 'E-mail',
		'The product is already in your wishlist!' => 'Produkt už v zozname prianí máte.',
		// Hosť nemá pomenovaný zoznam, {{wishlist_name}} by ostalo prázdne („do vášho "" zoznamu“).
		'{{product_name}} has been added to your {{wishlist_name}} list!'     => '{{product_name}} bol pridaný do zoznamu prianí.',
		'{{product_name}} has been removed from your {{wishlist_name}} list!' => '{{product_name}} bol odstránený zo zoznamu prianí.',
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
		// Preklad „Vybrať %s“ dosadí názov poľa v 1. páde („Vybrať krajina / región“),
		// preto názov v zátvorke: „Vyberte možnosť (krajina / región)“.
		'Select %s'                                     => 'Vyberte možnosť (%s)',
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
		// E-mail s prosbou o hodnotenie: v preklade WooCommerce je navyše „[“ (1.7.16).
		'Order #%1$s (%2$s)'            => 'Objednávka č. %1$s (%2$s)',
		// Mini košík: počítadlo pri nadpise „Košík“ nie je v preklade WooCommerce (1.7.18).
		// Jeden tvar pre všetky počty (text sa prepisuje aj v prehliadači), preto „ks“.
		'(items: %d)'                   => '(%d ks)',
		// Mini košík: texty, ktoré chýbajú v slovenskom preklade WooCommerce (11.2).
		'Your shopping cart is empty'   => 'Váš košík je prázdny',
		'Shipping and discounts calculated at checkout.' => 'Doprava a zľavy sa vypočítajú v pokladni.',
		'Number of items in the cart: %d' => 'Počet položiek v košíku: %d',
		'Number of items in the cart: %1$d. Total price of %2$s' => 'Počet položiek v košíku: %1$d. Celková cena: %2$s',
		'Shipping calculated at checkout.' => 'Doprava sa vypočíta v pokladni.',
		'Shipping and taxes calculated at checkout.' => 'Doprava a dane sa vypočítajú v pokladni.',
		'Taxes and discounts calculated at checkout.' => 'Dane a zľavy sa vypočítajú v pokladni.',
		// Produkt, galéria a pridanie do košíka (chýba v preklade WooCommerce 11.2).
		'Please select a valid quantity to add to the cart.' => 'Vyberte platné množstvo, ktoré chcete pridať do košíka.',
		'Please select some products to add to the cart.' => 'Vyberte produkty, ktoré chcete pridať do košíka.',
		'Please select product attributes before adding to cart.' => 'Pred pridaním do košíka vyberte vlastnosti produktu.',
		'Added to cart'                 => 'Pridané do košíka',
		'Previous image'                => 'Predchádzajúci obrázok',
		'Image thumbnails'              => 'Miniatúry obrázkov',
		'Full screen image'             => 'Obrázok na celú obrazovku',
		'Zoom in/out'                   => 'Priblížiť / oddialiť',
		'Filter'                        => 'Filtrovať',
		'Your account is using a temporary password. We emailed you a link to change your password.' => 'Používate dočasné heslo. Poslali sme vám e-mail s odkazom na zmenu hesla.',
		// E-maily zákazníkovi: pozastavená a zrušená objednávka.
		'Your order is on hold'         => 'Vaša objednávka je pozastavená',
		'Thanks for your order. We’ve received it, and it’s currently on hold while we complete the next required step, such as confirming, clearing, or finalizing payment.' => 'Ďakujeme za objednávku. Prijali sme ju a je pozastavená, kým nedokončíme ďalší potrebný krok, napríklad potvrdenie alebo prijatie platby.',
		'We’ll send another update when your order moves forward. Here’s a summary of your order:' => 'Keď sa objednávka posunie ďalej, pošleme vám ďalšiu správu. Tu je súhrn vašej objednávky:',
		'Order Cancelled: #%s'          => 'Objednávka zrušená: #%s',
		'Your order #%s has been cancelled.' => 'Vaša objednávka č. %s bola zrušená.',
		'We’re sorry to let you know that your order #%1$s has been cancelled.' => 'S ľútosťou vám oznamujeme, že vaša objednávka č. %1$s bola zrušená.',
		'We’re getting in touch to let you know that your order #%1$s has been cancelled.' => 'Oznamujeme vám, že vaša objednávka č. %1$s bola zrušená.',
		'Store Information'             => 'Informácie o obchode',
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

/**
 * Odpoveď predajkyne na hodnotenie (1.7.16): namiesto prihlasovacieho mena
 * (napr. „veronika.zabkova“) sa na webe ukáže „Odpoveď od Grace Art“.
 * Platí pre odpovede z účtu správcu obchodu; v admine sa meno nemení.
 */
function graceart_review_reply_author( $author, $comment_id, $comment ) {
	if ( is_admin() || ! $comment instanceof WP_Comment ) {
		return $author;
	}
	if ( 'review' !== $comment->comment_type || ! $comment->comment_parent || ! $comment->user_id ) {
		return $author;
	}
	if ( ! user_can( (int) $comment->user_id, 'manage_woocommerce' ) ) {
		return $author;
	}
	return __( 'Odpoveď od Grace Art', 'graceart' );
}
add_filter( 'get_comment_author', 'graceart_review_reply_author', 10, 3 );

/**
 * Pevná hlavička (zmenší sa po posunutí) a šípka „Späť hore“ v pätičke – assets/js/header.js.
 * Pokladňa: názvy produktov v súhrne objednávky sú odkazy na produkt – assets/js/checkout-summary.js.
 */
function graceart_header_script() {
	wp_enqueue_script( 'graceart-header', get_theme_file_uri( 'assets/js/header.js' ), array(), wp_get_theme()->get( 'Version' ), true );
	if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) {
		wp_enqueue_script( 'graceart-checkout-summary', get_theme_file_uri( 'assets/js/checkout-summary.js' ), array( 'wp-data' ), wp_get_theme()->get( 'Version' ), true );
	}
}
add_action( 'wp_enqueue_scripts', 'graceart_header_script' );

/**
 * E-maily WooCommerce v štýle témy (10. 11. 2026): nadpisy pätkovým písmom verzálkami,
 * tenké béžové čiary, oblé tlačidlá. Farby sú v WooCommerce → Nastavenia → E-maily.
 * (Písma webu sa do e-mailov nedajú spoľahlivo vložiť – Georgia a Helvetica sú bezpečné náhrady.)
 */
function graceart_email_styles( $css ) {
	return $css . '
h1, h2, h3 { font-family: Georgia, "Times New Roman", serif !important; font-weight: normal !important; text-transform: uppercase; letter-spacing: 0.04em; color: #3B2E25 !important; }
h1 { font-size: 26px !important; line-height: 1.25 !important; }
h2 { font-size: 20px !important; }
h3 { font-size: 16px !important; }
a { color: #8A5A3B !important; }
.td, .order_item td, table.td { border-color: #E6DCCF !important; }
#body_content table.td thead th, #body_content .order-totals-total th, #body_content .order-totals-total td { color: #3B2E25 !important; }
.email-order-detail-heading span { color: #7A6A5D !important; }
a.button, .button, .email-button { border-radius: 999px !important; }
#template_footer #credit, #template_footer #credit p { color: #7A6A5D !important; }
';
}
add_filter( 'woocommerce_email_styles', 'graceart_email_styles', 20 );

/**
 * NÁVRH štýlu PDF faktúry v štýle témy (10. 11. 2026) – doplnok k šablóne developera
 * (mu-plugin graceart-shop/templates/pdf/GraceArt), ktorú tým nemeníme: čierne čiary → béžové,
 * nadpisy a názov dokladu hnedé, hlavička tabuľky krémová. Ak sa developerovi páči,
 * presunie to do svojho style.css a túto funkciu zmažeme.
 */
function graceart_invoice_styles( $document_type ) {
	?>
body { color: #3B2E25; }
h1, td.document-title h1, .document-type-label { color: #8A5A3B; letter-spacing: 1pt; text-transform: uppercase; font-weight: normal; }
table.parties, table.parties td.supplier, table.order-details, table.order-details th, table.order-details td { border-color: #CDBBA7 !important; }
table.order-details thead th { background: #F7F3EE; color: #3B2E25; }
.party-label, .delivery-label { color: #8A5A3B; text-transform: uppercase; letter-spacing: 0.6pt; }
table.totals tr.order_total th, table.totals tr.order_total td, tr.order_total th, tr.order_total td { color: #3B2E25; border-top: 0.7pt solid #8A5A3B; }
	<?php
}
add_action( 'wpo_wcpdf_custom_styles', 'graceart_invoice_styles', 20 );

/**
 * Vlastné CSS témy.
 * Bolo v theme.json (styles.css), kde sa vypisovalo priamo do HTML každej stránky (~83 kB).
 * V samostatnom súbore ho prehliadač uloží do cache. Závislosť na 'global-styles' drží
 * rovnaké poradie ako predtým (po štýloch blokov a globálnych štýloch).
 */
function graceart_enqueue_custom_css() {
	// Zdroj je theme-custom.css; minifikovanú verziu vytvorí scripts/minify-theme-css.py.
	$path = 'assets/css/theme-custom.min.css';
	wp_enqueue_style(
		'graceart-custom',
		get_theme_file_uri( $path ),
		array( 'global-styles' ),
		(string) filemtime( get_theme_file_path( $path ) )
	);
}
add_action( 'wp_enqueue_scripts', 'graceart_enqueue_custom_css' );

function graceart_editor_custom_css() {
	add_editor_style( 'assets/css/theme-custom.css' );
}
add_action( 'after_setup_theme', 'graceart_editor_custom_css' );
