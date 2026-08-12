<?php

defined('ABSPATH') || exit;

global $product;

if (! $product instanceof WC_Product || ! $product->is_visible()) {
    return;
}

$hover_image = graceartProductHoverImageUrl($product);
$product_url = graceartProductLoopPermalink($product);

$graceart_loop_variation_data = graceartResolveSelectedVariationData($product);
$graceart_availability_product = $graceart_loop_variation_data
    ? (wc_get_product($graceart_loop_variation_data['variation_id']) ?: $product)
    : $product;
$graceart_availability = graceartAvailabilityShortLabel($graceart_availability_product);
?>

<div <?php wc_product_class(graceartProductLoopClasses($product), $product); ?>>
    <div class="product">
        <div class="product-thumb">
            <a href="<?php echo esc_url($product_url); ?>" class="image">
                <?php echo wp_kses_post(graceartProductBadgeHtml($product)); ?>
                <img src="<?php echo esc_url(graceartProductImageUrl($product)); ?>" alt="<?php echo esc_attr($product->get_name()); ?>">
                <?php if ($hover_image) : ?>
                    <img class="image-hover" src="<?php echo esc_url($hover_image); ?>" alt="<?php echo esc_attr($product->get_name()); ?>">
                <?php endif; ?>
            </a>
        </div>

        <div class="product-info">
            <div class="graceart-loop-top">
                <span class="price">
                    <?php echo wp_kses_post(graceartProductLoopPriceHtml($product)); ?>
                </span>
                <span class="graceart-loop-availability <?php echo $graceart_availability['in_stock'] ? 'is-in-stock' : 'is-backorder'; ?>">
                    <?php if ($graceart_availability['in_stock']) : ?><i class="fas fa-check"></i><?php endif; ?>
                    <?php echo esc_html($graceart_availability['label']); ?>
                </span>
                <a
                    href="<?php echo esc_url($product_url); ?>"
                    data-quantity="1"
                    data-product_id="<?php echo esc_attr($product->get_id()); ?>"
                    data-product_sku="<?php echo esc_attr($product->get_sku()); ?>"
                    class="graceart-loop-buy-button add_to_cart_button <?php echo $product->is_type('simple') && $product->supports('ajax_add_to_cart') ? 'ajax_add_to_cart' : ''; ?>"
                >
                    <?php esc_html_e('Kúpiť', 'graceart'); ?>
                </a>
            </div>
            <h6 class="title"><a href="<?php echo esc_url($product_url); ?>"><?php the_title(); ?></a></h6>
        </div>
    </div>
</div>
