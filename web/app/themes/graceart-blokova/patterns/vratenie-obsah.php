<?php
/**
 * Title: Vrátenie tovaru – odstúpenie od zmluvy a vzorový formulár
 * Slug: graceart/vratenie-obsah
 * Categories: graceart
 * Inserter: no
 * Description: Právo odstúpiť od zmluvy (zákon č. 108/2024 Z. z.), postup v 3 krokoch, vrátenie platby, výnimky a vzorový formulár s PDF. Rozloženie ako stránka Doprava a platba.
 */
$graceart_seller  = array_merge( array( GRACEART_PREDAJCA . ' (Grace Art)' ), graceart_predajca_adresa() );
$graceart_ico     = trim( (string) get_option( 'graceart_company_ico', '' ) );
$graceart_email   = graceart_kontakt_email();
$graceart_address = implode( ', ', array_filter( array_merge( $graceart_seller, array( '' !== $graceart_ico ? 'IČO: ' . $graceart_ico : '', $graceart_email ) ) ) );
$graceart_steps   = array(
	array( '01', 'Oznámte mi to', 'Do 14 dní od prevzatia mi napíšte e-mail alebo list, že odstupujete od zmluvy. Môžete použiť vzorový formulár nižšie.' ),
	array( '02', 'Pošlite tovar späť', 'Tovar mi pošlite najneskôr do 14 dní od odstúpenia, dobre zabalený a s číslom objednávky. Zásielku odporúčam poistiť.' ),
	array( '03', 'Vrátim vám peniaze', 'Do 14 dní od oznámenia vám vrátim všetky platby vrátane dopravy k vám, rovnakým spôsobom, akým ste platili.' ),
);
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"96px","bottom":"32px","left":"32px","right":"32px"}}},"layout":{"type":"constrained","contentSize":"960px"}} -->
<div class="wp-block-group alignfull" style="padding-top:96px;padding-right:32px;padding-bottom:32px;padding-left:32px"><!-- wp:paragraph {"className":"graceart-delivery-intro"} -->
<p class="graceart-delivery-intro">Ako spotrebiteľ môžete od kúpy odstúpiť do 14 dní od prevzatia tovaru, aj bez udania dôvodu. Na dodržanie lehoty stačí, ak mi oznámenie pošlete pred jej uplynutím.</p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"graceart-free-shipping","layout":{"type":"default"}} -->
<div class="wp-block-group graceart-free-shipping"><!-- wp:paragraph {"className":"graceart-free-shipping-number"} -->
<p class="graceart-free-shipping-number">14 dní</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>na vrátenie tovaru bez udania dôvodu</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"64px","bottom":"0","left":"32px","right":"32px"}}},"layout":{"type":"constrained","contentSize":"960px"}} -->
<div class="wp-block-group alignfull" style="padding-top:64px;padding-right:32px;padding-bottom:0;padding-left:32px"><!-- wp:paragraph {"className":"graceart-eyebrow"} -->
<p class="graceart-eyebrow">Postup</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Ako vrátiť tovar</h2>
<!-- /wp:heading -->

<!-- wp:columns {"className":"graceart-return-steps","style":{"spacing":{"margin":{"top":"32px"},"blockGap":{"left":"24px","top":"16px"}}}} -->
<div class="wp-block-columns graceart-return-steps" style="margin-top:32px"><?php foreach ( $graceart_steps as $graceart_step ) : ?><!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph {"className":"graceart-step-num"} -->
<p class="graceart-step-num"><?php echo esc_html( $graceart_step[0] ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php echo esc_html( $graceart_step[1] ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php echo esc_html( $graceart_step[2] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --><?php endforeach; ?></div>
<!-- /wp:columns -->

<!-- wp:columns {"className":"graceart-delivery-info","style":{"spacing":{"margin":{"top":"48px"},"blockGap":{"left":"48px","top":"32px"}}}} -->
<div class="wp-block-columns graceart-delivery-info" style="margin-top:48px"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Vrátenie platby</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Pri doprave vraciam cenu najlacnejšieho štandardného spôsobu, ktorý som ponúkala. Platbu môžem zadržať, kým mi tovar nevrátite alebo nepredložíte doklad o jeho odoslaní.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Náklady a stav tovaru</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Náklady na vrátenie tovaru znášate vy. Za zníženie hodnoty tovaru zaobchádzaním nad rámec potrebný na zistenie jeho povahy a vlastností zodpovedáte vy.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Kedy odstúpiť nemožno</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Pri tovare vyrobenom alebo upravenom podľa vašich osobitných požiadaviek a v ďalších prípadoch podľa zákona.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"80px","bottom":"96px","left":"32px","right":"32px"}}},"layout":{"type":"constrained","contentSize":"960px"}} -->
<div class="wp-block-group alignfull" style="padding-top:80px;padding-right:32px;padding-bottom:96px;padding-left:32px"><!-- wp:paragraph {"className":"graceart-eyebrow"} -->
<p class="graceart-eyebrow">Formulár</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Vzorový formulár na odstúpenie od zmluvy</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Formulár nie je povinný – stačí akékoľvek jasné oznámenie e-mailom na <a href="mailto:<?php echo esc_attr( $graceart_email ); ?>"><?php echo esc_html( $graceart_email ); ?></a> alebo listom na adresu nižšie.</p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"graceart-returns-form","layout":{"type":"default"}} -->
<div class="wp-block-group graceart-returns-form"><!-- wp:paragraph -->
<p><em>(vyplňte a zašlite tento formulár iba vtedy, ak chcete odstúpiť od zmluvy)</em></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong>Adresát:</strong> <?php echo esc_html( $graceart_address ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Oznamujem, že odstupujem od zmluvy o kúpe tohto tovaru: …………………………</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Číslo objednávky: …………………………</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Dátum objednania / dátum prijatia tovaru: …………………………</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Meno a priezvisko spotrebiteľa: …………………………</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Adresa spotrebiteľa: …………………………</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Podpis spotrebiteľa (iba ak sa formulár podáva v listinnej podobe): …………………………</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Dátum: …………………………</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:html -->
<p class="graceart-download"><a class="wp-element-button" href="<?php echo esc_url( add_query_arg( 'ver', wp_get_theme()->get( 'Version' ), get_theme_file_uri( 'assets/docs/formular-na-odstupenie-od-zmluvy.pdf' ) ) ); ?>" download>Stiahnuť formulár (PDF)</a></p>
<!-- /wp:html -->

<!-- wp:paragraph {"className":"graceart-delivery-links"} -->
<p class="graceart-delivery-links">Reklamácie chybného tovaru (záručná doba 24 mesiacov) sú samostatný postup, ktorý nájdete v <a href="/obchodne-podmienky/">Obchodných podmienkach</a>. Ceny dopravy nájdete na stránke <a href="/doprava-a-platba/">Doprava a platba</a>.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
