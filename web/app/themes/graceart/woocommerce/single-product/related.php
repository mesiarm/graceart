<?php

defined('ABSPATH') || exit;

if (! $related_products) {
    return;
}

$heading = apply_filters('woocommerce_product_related_products_heading', __('Related products', 'woocommerce'));
$related_ids = array_map(function (WC_Product $related_product): int {
    return $related_product->get_id();
}, $related_products);
?>

<section class="related products">
    <?php if ($heading) : ?>
        <h2><?php echo esc_html($heading); ?></h2>
    <?php endif; ?>

    <div class="products row row-cols-xl-4 row-cols-lg-3 row-cols-md-2 row-cols-sm-2 row-cols-1">
        <?php
        $related_query = new WP_Query([
            'post_type' => 'product',
            'post__in' => $related_ids,
            'orderby' => 'post__in',
            'posts_per_page' => count($related_ids),
        ]);

while ($related_query->have_posts()) :
    $related_query->the_post();

    global $product;
    $product = wc_get_product(get_the_ID());

    wc_get_template_part('content', 'product');
endwhile;

wp_reset_postdata();
?>
    </div>
</section>
