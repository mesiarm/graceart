<?php
/**
 * Title: Novinky v ponuke
 * Slug: graceart/novinky
 * Categories: graceart
 * Description: Nadpis a 8 najnovších produktov z obchodu.
 */
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"96px","bottom":"0","left":"32px","right":"32px"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull" style="padding-top:96px;padding-right:32px;padding-bottom:0;padding-left:32px"><!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
<div class="wp-block-group"><!-- wp:heading -->
<h2 class="wp-block-heading">Novinky v ponuke</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"graceart-category-link"} -->
<p class="graceart-category-link"><a href="/produkty/">Všetky produkty →</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:shortcode -->
[products limit="8" columns="4" orderby="date" order="DESC" visibility="visible"]
<!-- /wp:shortcode --></div>
<!-- /wp:group -->
