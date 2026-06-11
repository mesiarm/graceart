<?php

defined('ABSPATH') || exit;

global $product;

if (! $product instanceof WC_Product || ! $product->is_visible()) {
    return;
}

$hover_image = graceartProductHoverImageUrl($product);
?>

<div <?php wc_product_class(graceartProductLoopClasses($product), $product); ?>>
    <div class="product">
        <div class="product-thumb">
            <a href="<?php the_permalink(); ?>" class="image">
                <?php echo wp_kses_post(graceartProductBadgeHtml($product)); ?>
                <img src="<?php echo esc_url(graceartProductImageUrl($product)); ?>" alt="<?php echo esc_attr($product->get_name()); ?>">
                <?php if ($hover_image) : ?>
                    <img class="image-hover" src="<?php echo esc_url($hover_image); ?>" alt="<?php echo esc_attr($product->get_name()); ?>">
                <?php endif; ?>
            </a>
        </div>

        <div class="product-info">
            <h6 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h6>
            <span class="price">
                <?php echo wp_kses_post($product->get_price_html()); ?>
            </span>
            <div class="product-buttons">
                <a href="<?php the_permalink(); ?>" class="product-button hintT-top" data-hint="<?php esc_attr_e('Detail produktu', 'graceart'); ?>"><i class="fas fa-search"></i></a>
                <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" data-quantity="1" data-product_id="<?php echo esc_attr($product->get_id()); ?>" data-product_sku="<?php echo esc_attr($product->get_sku()); ?>" class="product-button hintT-top add_to_cart_button <?php echo $product->supports('ajax_add_to_cart') ? 'ajax_add_to_cart' : ''; ?>" data-hint="<?php echo esc_attr($product->add_to_cart_text()); ?>"><i class="fas fa-shopping-cart"></i></a>
            </div>
        </div>
    </div>
</div>
