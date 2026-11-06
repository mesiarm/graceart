<?php
/**
 * Title: Kontakt – otázky a odpovede
 * Slug: graceart/kontakt-otazky
 * Categories: graceart
 * Description: Časté otázky (rozbaľovacie) na stránke Kontakt. Odpovede vychádzajú z obchodných podmienok.
 */
$graceart_faq = array(
	array(
		'Robíte zápisníky aj na mieru?',
		'Áno. Napíšte mi, aký rozmer, farbu kože, papier alebo motív si predstavujete, a pripravím vám návrh aj s cenou.',
	),
	array(
		'Ako dlho trvá, kým zápisník dostanem?',
		'Výrobky skladom odosielam do 2 – 3 pracovných dní. Pri výrobkoch na objednávku je doba odoslania uvedená priamo pri produkte.',
	),
	array(
		'Kam posielate a koľko stojí doprava?',
		'Zásielky posielam do týchto krajín: ' . do_shortcode( '[graceart_krajiny_dodania]' ) . '. Pri objednávke ' . do_shortcode( '[graceart_doprava_zdarma]' ) . ' je doprava zadarmo. Ceny a spôsoby doručenia nájdete na stránke <a href="/doprava-a-platba/">Doprava a platba</a>. Ak potrebujete doručiť inam, napíšte mi.',
	),
	array(
		'Môžem tovar vrátiť?',
		'Áno, do 14 dní od prevzatia aj bez udania dôvodu (okrem výrobkov na zákazku). Postup nájdete na stránke <a href="/vratenie-tovaru/">Vrátenie tovaru</a>.',
	),
);
?>
<!-- wp:group {"align":"full","backgroundColor":"cream","style":{"spacing":{"padding":{"top":"96px","bottom":"96px","left":"32px","right":"32px"}}},"layout":{"type":"constrained","contentSize":"820px"}} -->
<div class="wp-block-group alignfull has-cream-background-color has-background" style="padding-top:96px;padding-right:32px;padding-bottom:96px;padding-left:32px"><!-- wp:paragraph {"align":"center","className":"graceart-eyebrow"} -->
<p class="has-text-align-center graceart-eyebrow">Otázky a odpovede</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","style":{"spacing":{"margin":{"bottom":"40px"}}}} -->
<h2 class="wp-block-heading has-text-align-center" style="margin-bottom:40px">Čo sa ma často pýtate</h2>
<!-- /wp:heading -->

<?php foreach ( $graceart_faq as $graceart_item ) : ?>
<!-- wp:details {"className":"graceart-faq-item"} -->
<details class="wp-block-details graceart-faq-item"><summary><?php echo esc_html( $graceart_item[0] ); ?></summary><!-- wp:paragraph -->
<p><?php echo wp_kses( $graceart_item[1], array( 'a' => array( 'href' => array() ) ) ); ?></p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<?php endforeach; ?></div>
<!-- /wp:group -->
