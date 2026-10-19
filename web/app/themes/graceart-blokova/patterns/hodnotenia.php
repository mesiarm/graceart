<?php
/**
 * Title: Hodnotenia zákazníkov
 * Slug: graceart/hodnotenia
 * Categories: graceart
 * Description: Tri hodnotenia zákazníkov zo SAShE.sk.
 */
$graceart_reviews = array(
	array( 'Nádherný zápisník, krásne zabalený.', 'Martina P.' ),
	array( 'Rýchle dodanie a veľmi precízne prevedenie.', 'ivusiik' ),
	array( 'Veľká vďaka za ochotu i dodanie včas.', 'Mar12' ),
);
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"96px","bottom":"96px","left":"32px","right":"32px"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull" style="padding-top:96px;padding-right:32px;padding-bottom:96px;padding-left:32px"><!-- wp:heading -->
<h2 class="wp-block-heading">Čo hovoria zákazníci</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"muted","fontSize":"small"} -->
<p class="has-muted-color has-text-color has-small-font-size">Hodnotenia zo <a href="https://www.sashe.sk/GraceArt">SAShE.sk</a></p>
<!-- /wp:paragraph -->

<!-- wp:columns {"style":{"spacing":{"margin":{"top":"36px"}}}} -->
<div class="wp-block-columns" style="margin-top:36px"><?php foreach ( $graceart_reviews as $graceart_review ) : ?><!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"graceart-review","layout":{"type":"default"}} -->
<div class="wp-block-group graceart-review"><!-- wp:paragraph {"className":"graceart-review-stars"} -->
<p class="graceart-review-stars">★★★★★</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"graceart-review-text"} -->
<p class="graceart-review-text">„<?php echo esc_html( $graceart_review[0] ); ?>“</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"graceart-review-name"} -->
<p class="graceart-review-name"><?php echo esc_html( $graceart_review[1] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column --><?php endforeach; ?></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
