<?php
/**
 * Title: O mne – príbeh, postup, citát a materiály
 * Slug: graceart/o-mne-obsah
 * Categories: graceart
 * Description: Obsah stránky O mne: príbeh s fotkou, ako vzniká zápisník, citát a materiály.
 */
$graceart_steps = array(
	array( '01', 'Strih kože', 'Kožu strihám ručne na mieru každého zápisníka.', '[FOTO: strih kože]' ),
	array( '02', 'Príprava papiera', 'Papier skladám do zložiek, z ktorých vznikne vnútro zápisníka.', '[FOTO: skladanie papiera]' ),
	array( '03', 'Ručné šitie väzby', 'Zložky prišívam k doskám ručne, steh po stehu.', '[FOTO: šitie väzby]' ),
	array( '04', 'Dekorovanie dosiek', 'Nakoniec dosky dotváram, od výrazných farieb po jemné kreslené ilustrácie.', '[FOTO: maľovanie dosiek]' ),
);
$graceart_materials = array(
	array(
		'Pravá koža',
		'[PÔVOD A DRUH KOŽE: napr. odkiaľ ju beriete a prečo práve táto.]',
		'<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#8A5A3B" stroke-width="1.3" stroke-linejoin="round" aria-hidden="true"><path d="M6 3h9l3 3v15H6z"></path><path d="M15 3v3h3"></path><path d="M9 12h6M9 16h4"></path></svg>',
	),
	array(
		'Archívny papier',
		'[GRAMÁŽ A DRUH PAPIERA: napr. vhodný na pero, akvarel, fotografie.]',
		'<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#8A5A3B" stroke-width="1.3" stroke-linejoin="round" aria-hidden="true"><path d="M4 5c3-1 5-1 8 1 3-2 5-2 8-1v14c-3-1-5-1-8 1-3-2-5-2-8-1z"></path><path d="M12 6v14"></path></svg>',
	),
	array(
		'Ručne šitá väzba',
		'[TECHNIKY VÄZBY: napr. koptská, dlhý steh, väzba s vymeniteľným vnútrom.]',
		'<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#8A5A3B" stroke-width="1.3" stroke-linecap="round" aria-hidden="true"><path d="M5 4v16M19 4v16"></path><path d="M5 7l14 3M5 12l14 3M5 17l14 3" stroke-dasharray="2 2"></path></svg>',
	),
);
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"112px","bottom":"96px","left":"32px","right":"32px"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull" style="padding-top:112px;padding-right:32px;padding-bottom:96px;padding-left:32px"><!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"left":"72px"}}}} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:image {"aspectRatio":"4/5","scale":"cover","sizeSlug":"full","linkDestination":"none","className":"graceart-about-photo"} -->
<figure class="wp-block-image size-full graceart-about-photo"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/o-mne.jpg' ) ); ?>" alt="Ručne viazaný zápisník s ilustráciou stromu v rukách" style="aspect-ratio:4/5;object-fit:cover"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:paragraph {"className":"graceart-eyebrow"} -->
<p class="graceart-eyebrow">Príbeh Grace Art</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Láska k papieru a koži</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Zápisníky, diáre a fotoalbumy viažem a šijem ručne. Skúšam rôzne techniky väzby a dekorovania dosiek, od výrazných farieb po jemné kreslené ilustrácie.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>[AKO TO ZAČALO: 2–3 vety o tom, kedy a prečo ste začali viazať zápisníky.]</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>[ČO VÁS BAVÍ NAJVIAC: 1–2 vety o obľúbenej technike, materiáli alebo motíve.]</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"graceart-signature"} -->
<p class="graceart-signature">[Vaše meno]</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"16px","bottom":"0","left":"32px","right":"32px"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull" style="padding-top:16px;padding-right:32px;padding-bottom:0;padding-left:32px"><!-- wp:group {"layout":{"type":"constrained","contentSize":"640px","justifyContent":"left"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"className":"graceart-eyebrow"} -->
<p class="graceart-eyebrow">Remeslo</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Ako vzniká zápisník</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Od strihu kože až po posledný steh. Každý zápisník vzniká ručne, kus po kuse.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:columns {"style":{"spacing":{"margin":{"top":"56px"},"blockGap":{"left":"24px"}}}} -->
<div class="wp-block-columns" style="margin-top:56px"><?php foreach ( $graceart_steps as $graceart_step ) : ?><!-- wp:column {"className":"graceart-step"} -->
<div class="wp-block-column graceart-step"><!-- wp:group {"className":"graceart-step-photo","layout":{"type":"default"}} -->
<div class="wp-block-group graceart-step-photo"><!-- wp:paragraph -->
<p><?php echo esc_html( $graceart_step[3] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:paragraph {"className":"graceart-step-num"} -->
<p class="graceart-step-num"><?php echo esc_html( $graceart_step[0] ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php echo esc_html( $graceart_step[1] ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php echo esc_html( $graceart_step[2] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --><?php endforeach; ?></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","backgroundColor":"cream","style":{"spacing":{"padding":{"top":"104px","bottom":"104px","left":"32px","right":"32px"},"margin":{"top":"112px"}}},"layout":{"type":"constrained","contentSize":"900px"}} -->
<div class="wp-block-group alignfull has-cream-background-color has-background" style="margin-top:112px;padding-top:104px;padding-right:32px;padding-bottom:104px;padding-left:32px"><!-- wp:paragraph {"align":"center","className":"graceart-big-quote"} -->
<p class="has-text-align-center graceart-big-quote">„Každý kus vzniká samostatne, preto žiadne dva nie sú úplne rovnaké.“</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"112px","bottom":"112px","left":"32px","right":"32px"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull" style="padding-top:112px;padding-right:32px;padding-bottom:112px;padding-left:32px"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">S čím pracujem</h2>
<!-- /wp:heading -->

<!-- wp:columns {"style":{"spacing":{"margin":{"top":"48px"},"blockGap":{"left":"24px"}}}} -->
<div class="wp-block-columns" style="margin-top:48px"><?php foreach ( $graceart_materials as $graceart_material ) : ?><!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"graceart-material","layout":{"type":"default"}} -->
<div class="wp-block-group graceart-material"><!-- wp:html -->
<?php echo $graceart_material[2]; // Pevne zadaná ikona z tohto súboru. ?>
<!-- /wp:html -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php echo esc_html( $graceart_material[0] ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php echo esc_html( $graceart_material[1] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column --><?php endforeach; ?></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
