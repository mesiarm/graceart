<?php
/**
 * Title: Hlavička stránky O mne (text vľavo, fotka vpravo)
 * Slug: graceart/o-mne-hlavicka
 * Categories: graceart
 * Inserter: no
 * Description: Hlavička podstránky s názvom stránky a drobčekmi, variant D.
 */
?>
<!-- wp:group {"align":"full","className":"graceart-cat-header","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull graceart-cat-header"><!-- wp:columns {"verticalAlignment":"center","isStackedOnMobile":true,"style":{"spacing":{"blockGap":{"left":"0"},"margin":{"top":"0","bottom":"0"}}}} -->
<div class="wp-block-columns are-vertically-aligned-center" style="margin-top:0;margin-bottom:0"><!-- wp:column {"verticalAlignment":"center","width":"55%","style":{"spacing":{"padding":{"top":"56px","bottom":"56px","left":"max(32px, calc((100vw - 1240px) / 2))","right":"48px"}}}} -->
<div class="wp-block-column is-vertically-aligned-center" style="padding-top:56px;padding-right:48px;padding-bottom:56px;padding-left:max(32px, calc((100vw - 1240px) / 2));flex-basis:55%"><!-- wp:post-title {"level":1,"style":{"typography":{"fontSize":"48px"}}} /-->

<!-- wp:yoast-seo/breadcrumbs /--></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"45%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:45%"><!-- wp:image {"aspectRatio":"16/9","scale":"cover","sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/hero.jpg' ) ); ?>" alt="<?php esc_attr_e( 'Hnedý kožený zápisník s ovinutým remienkom v ženských rukách', 'graceart' ); ?>" style="aspect-ratio:16/9;object-fit:cover"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
