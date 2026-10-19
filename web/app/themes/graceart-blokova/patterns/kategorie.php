<?php
/**
 * Title: Kolekcie (4 kategórie)
 * Slug: graceart/kategorie
 * Categories: graceart
 * Description: Štyri kategórie s veľkou fotkou, názvom a odkazom.
 */
$graceart_categories = array(
	array( 'Kožené diáre 2027', '/kategoria-produktu/kozene-diare-2027/', 'kat-diare.jpg' ),
	array( 'Kožené zápisníky', '/kategoria-produktu/kozene-zapisniky/', 'kat-zapisniky.jpg' ),
	array( 'Kožené fotoalbumy', '/kategoria-produktu/knihy/', 'kat-fotoalbumy.jpg' ),
	array( 'Autorské zápisníky', '/kategoria-produktu/autorske-zapisniky/', 'kat-autorske.jpg' ),
);
?>
<!-- wp:group {"align":"full","className":"graceart-categories","style":{"spacing":{"padding":{"top":"96px","bottom":"0","left":"32px","right":"32px"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull graceart-categories" style="padding-top:96px;padding-right:32px;padding-bottom:0;padding-left:32px"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Kolekcie</h2>
<!-- /wp:heading -->

<!-- wp:columns {"style":{"spacing":{"margin":{"top":"40px"}}}} -->
<div class="wp-block-columns" style="margin-top:40px"><?php foreach ( $graceart_categories as $graceart_cat ) : ?><!-- wp:column -->
<div class="wp-block-column"><!-- wp:image {"aspectRatio":"3/4","scale":"cover","sizeSlug":"full","linkDestination":"custom"} -->
<figure class="wp-block-image size-full"><a href="<?php echo esc_url( $graceart_cat[1] ); ?>"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/' . $graceart_cat[2] ) ); ?>" alt="<?php echo esc_attr( $graceart_cat[0] ); ?>" style="aspect-ratio:3/4;object-fit:cover"/></a></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"align":"center","className":"graceart-category-name"} -->
<p class="has-text-align-center graceart-category-name"><a href="<?php echo esc_url( $graceart_cat[1] ); ?>"><?php echo esc_html( $graceart_cat[0] ); ?></a></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"align":"center","className":"graceart-category-link"} -->
<p class="has-text-align-center graceart-category-link"><a href="<?php echo esc_url( $graceart_cat[1] ); ?>">Prezrieť →</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --><?php endforeach; ?></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
