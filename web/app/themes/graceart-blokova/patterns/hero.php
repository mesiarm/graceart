<?php
/**
 * Title: Úvodný banner
 * Slug: graceart/hero
 * Categories: graceart
 * Description: Fotka cez celú šírku s nadpisom a tlačidlom Pozrieť zápisníky v strede.
 */
$graceart_hero = esc_url( get_theme_file_uri( 'assets/images/hero.jpg' ) );
?>
<!-- wp:cover {"url":"<?php echo $graceart_hero; ?>","alt":"<?php esc_attr_e( 'Hnedý kožený zápisník s ovinutým remienkom v ženských rukách', 'graceart' ); ?>","dimRatio":40,"customOverlayColor":"#281c14","isUserOverlayColor":true,"minHeight":640,"minHeightUnit":"px","contentPosition":"center center","isDark":true,"align":"full","className":"graceart-hero","layout":{"type":"constrained","contentSize":"760px"}} -->
<div class="wp-block-cover alignfull is-dark graceart-hero" style="min-height:640px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-40 has-background-dim" style="background-color:#281c14"></span><img class="wp-block-cover__image-background" alt="<?php esc_attr_e( 'Hnedý kožený zápisník s ovinutým remienkom v ženských rukách', 'graceart' ); ?>" src="<?php echo $graceart_hero; ?>" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:heading {"textAlign":"center","level":1,"textColor":"base"} -->
<h1 class="wp-block-heading has-text-align-center has-base-color has-text-color">Kožené zápisníky, ktoré vás budú sprevádzať roky</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","textColor":"base","fontSize":"large"} -->
<p class="has-text-align-center has-base-color has-text-color has-large-font-size">Pravá koža, archívny papier a ručne šitá väzba. Každý kus vzniká samostatne, od strihu kože až po posledný steh.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"36px"}}}} -->
<div class="wp-block-buttons" style="margin-top:36px"><!-- wp:button {"backgroundColor":"base","textColor":"ink"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-ink-color has-base-background-color has-text-color has-background wp-element-button" href="/produkty/">Pozrieť zápisníky</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:cover -->
