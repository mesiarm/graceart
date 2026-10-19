<?php
/**
 * Title: Hlavička kategórie (text vľavo, fotka vpravo)
 * Slug: graceart/kategoria-hlavicka
 * Categories: graceart
 * Inserter: no
 * Description: Hlavička stránky kategórie s ozdobným slovom, názvom kategórie a fotkou vpravo.
 */
?>
<!-- wp:group {"align":"full","className":"graceart-cat-header","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull graceart-cat-header"><!-- wp:columns {"verticalAlignment":"center","isStackedOnMobile":true,"style":{"spacing":{"blockGap":{"left":"0"},"margin":{"top":"0","bottom":"0"}}}} -->
<div class="wp-block-columns are-vertically-aligned-center" style="margin-top:0;margin-bottom:0"><!-- wp:column {"verticalAlignment":"center","width":"55%","style":{"spacing":{"padding":{"top":"56px","bottom":"56px","left":"max(32px, calc((100vw - 1240px) / 2))","right":"48px"}}}} -->
<div class="wp-block-column is-vertically-aligned-center" style="padding-top:56px;padding-right:48px;padding-bottom:56px;padding-left:max(32px, calc((100vw - 1240px) / 2));flex-basis:55%"><!-- wp:paragraph {"className":"graceart-script","textColor":"brown","fontSize":"x-large"} -->
<p class="graceart-script has-brown-color has-text-color has-x-large-font-size">kolekcia</p>
<!-- /wp:paragraph -->

<!-- wp:query-title {"type":"archive","showPrefix":false,"style":{"typography":{"fontSize":"48px"}}} /-->

<!-- wp:woocommerce/breadcrumbs /--></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"45%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:45%"><!-- wp:image {"aspectRatio":"16/9","scale":"cover","sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/kat-zapisniky.jpg' ) ); ?>" alt="" style="aspect-ratio:16/9;object-fit:cover"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
