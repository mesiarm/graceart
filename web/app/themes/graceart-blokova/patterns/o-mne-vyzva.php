<?php
/**
 * Title: Výzva na nákup (fotka cez celú šírku)
 * Slug: graceart/o-mne-vyzva
 * Categories: graceart
 * Description: Fotka s nadpisom „Nájdite si svoj kúsok“ a tlačidlami Pozrieť zápisníky a Napíšte mi.
 */
$graceart_cta = esc_url( get_theme_file_uri( 'assets/images/o-mne.webp' ) );
?>
<!-- wp:cover {"url":"<?php echo $graceart_cta; ?>","alt":"<?php esc_attr_e( 'Ručne viazaný zápisník s ilustráciou stromu v rukách', 'graceart' ); ?>","dimRatio":60,"customOverlayColor":"#281c14","isUserOverlayColor":true,"minHeight":420,"minHeightUnit":"px","contentPosition":"center center","isDark":true,"align":"full","className":"graceart-hero","layout":{"type":"constrained","contentSize":"680px"}} -->
<div class="wp-block-cover alignfull is-dark graceart-hero" style="min-height:420px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-60 has-background-dim" style="background-color:#281c14"></span><img class="wp-block-cover__image-background" alt="<?php esc_attr_e( 'Ručne viazaný zápisník s ilustráciou stromu v rukách', 'graceart' ); ?>" src="<?php echo $graceart_cta; ?>" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:heading {"textAlign":"center","textColor":"base"} -->
<h2 class="wp-block-heading has-text-align-center has-base-color has-text-color">Nájdite si svoj kúsok</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","textColor":"base","fontSize":"large"} -->
<p class="has-text-align-center has-base-color has-text-color has-large-font-size">Zápisníky, diáre a fotoalbumy, ktoré sú pripravené na odoslanie.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"36px"}}}} -->
<div class="wp-block-buttons" style="margin-top:36px"><!-- wp:button {"backgroundColor":"base","textColor":"ink"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-ink-color has-base-background-color has-text-color has-background wp-element-button" href="/produkty/">Pozrieť zápisníky</a></div>
<!-- /wp:button -->

<!-- wp:button {"textColor":"base","className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link has-base-color has-text-color wp-element-button" href="/kontakt/">Napíšte mi</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:cover -->
