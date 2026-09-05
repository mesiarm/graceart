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

// With "redirect to cart after adding" on, the button has to be a real
// add-to-cart link — an AJAX add would keep the shopper on the listing.
$graceart_redirect_after_add = get_option('woocommerce_cart_redirect_after_add') === 'yes';
// Cart-aware: a one-off piece already sitting in the cart cannot be added
// again, and WooCommerce would answer the click with an error notice.
$graceart_can_add_more = graceartCanAddToCart($graceart_availability_product);

$graceart_can_add_directly = $product->is_type('simple')
    && $product->is_purchasable()
    && $product->is_in_stock()
    && $graceart_can_add_more;

// A variable product can be bought straight from the listing too, but only when
// the loop has resolved one concrete, buyable variation and every attribute of
// it has a value. Anything less and the shopper has to pick options first.
$graceart_variation_add = null;

if ($product->is_type('variable') && $graceart_loop_variation_data) {
    $graceart_variation_attributes = $graceart_loop_variation_data['attributes'] ?? [];

    if (
        ! empty($graceart_loop_variation_data['variation_id'])
        && ! empty($graceart_loop_variation_data['is_purchasable'])
        && ! empty($graceart_loop_variation_data['is_in_stock'])
        && $graceart_variation_attributes
        && ! in_array('', array_map('strval', $graceart_variation_attributes), true)
        && $graceart_can_add_more
    ) {
        $graceart_variation_add = $graceart_loop_variation_data;
    }
}

$graceart_ajax_add = $graceart_can_add_directly
    && ! $graceart_redirect_after_add
    && get_option('woocommerce_enable_ajax_add_to_cart') === 'yes'
    && $product->supports('ajax_add_to_cart');

$graceart_buy_url = $product_url;
$graceart_buy_label = __('Kúpiť', 'graceart');

// Everything available is already in the cart — send them there rather than
// offering an add that cannot succeed.
if (! $graceart_can_add_more && $graceart_availability_product->is_in_stock()) {
    $graceart_buy_url = wc_get_cart_url();
    $graceart_buy_label = __('V košíku', 'graceart');
}

if ($graceart_redirect_after_add && $graceart_can_add_directly) {
    $graceart_buy_url = $product->add_to_cart_url();
} elseif ($graceart_redirect_after_add && $graceart_variation_add) {
    // Same shape WooCommerce's own variable handler expects on a GET request.
    $graceart_buy_url = remove_query_arg('added-to-cart', add_query_arg(array_merge(
        [
            'add-to-cart' => $product->get_id(),
            'variation_id' => (int) $graceart_variation_add['variation_id'],
        ],
        $graceart_variation_add['attributes']
    )));
}
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
                    href="<?php echo esc_url($graceart_buy_url); ?>"
                    data-quantity="1"
                    data-product_id="<?php echo esc_attr($product->get_id()); ?>"
                    data-product_sku="<?php echo esc_attr($product->get_sku()); ?>"
                    class="graceart-loop-buy-button add_to_cart_button <?php echo $graceart_ajax_add ? 'ajax_add_to_cart' : ''; ?>"
                >
                    <?php echo esc_html($graceart_buy_label); ?>
                </a>
            </div>
            <h6 class="title"><a href="<?php echo esc_url($product_url); ?>"><?php the_title(); ?></a></h6>
        </div>
    </div>
</div>
