<?php

defined('ABSPATH') || exit;

global $product;

if (! $product instanceof WC_Product || ! $product->is_visible()) {
    return;
}

$product_url = graceartProductLoopPermalink($product);

// Cart-aware: a one-off piece already sitting in the cart cannot be added
// again, and WooCommerce would answer the click with an error notice.
$graceart_can_add_more = graceartCanAddToCart($product);
$graceart_has_addable_stock = graceartHasAddableStock($product);

$graceart_can_add_directly = $product->is_type('simple')
    && $product->is_purchasable()
    && $product->is_in_stock()
    && $graceart_can_add_more;

// With "redirect to cart after adding" on, the button has to be a real
// add-to-cart link — an AJAX add would keep the shopper on the listing.
$graceart_redirect_after_add = get_option('woocommerce_cart_redirect_after_add') === 'yes';

$graceart_ajax_add = $graceart_can_add_directly
    && ! $graceart_redirect_after_add
    && get_option('woocommerce_enable_ajax_add_to_cart') === 'yes'
    && $product->supports('ajax_add_to_cart');

$graceart_buy_url = $product_url;
$graceart_buy_label = __('Pridať do košíka', 'graceart');

// No button when there is nothing to add: sold out, or a one-off piece whose
// whole stock is already in the cart. The card still links to the product.
$graceart_show_button = ($product->is_type('variable') && $graceart_has_addable_stock) || $graceart_can_add_directly;

if ($product->is_type('variable')) {
    // A variable product is chosen on its own page, not from the listing.
    $graceart_buy_label = __('Vybrať variant', 'graceart');
} elseif ($graceart_redirect_after_add && $graceart_can_add_directly) {
    $graceart_buy_url = $product->add_to_cart_url();
}
?>

<div <?php wc_product_class(graceartProductLoopClasses($product), $product); ?>>
    <div class="product">
        <div class="product-thumb">
            <a href="<?php echo esc_url($product_url); ?>" class="image">
                <?php echo wp_kses_post(graceartProductBadgeHtml($product)); ?>
                <img src="<?php echo esc_url(graceartProductImageUrl($product)); ?>"<?php echo graceartProductImageSizeAttr($product); ?> alt="<?php echo esc_attr($product->get_name()); ?>" loading="lazy" decoding="async">
            </a>
        </div>

        <div class="product-info">
            <h6 class="title"><a href="<?php echo esc_url($product_url); ?>"><?php the_title(); ?></a></h6>
            <span class="price">
                <?php echo wp_kses_post(graceartProductLoopPriceHtml($product)); ?>
            </span>
            <?php if ($graceart_show_button) : ?>
            <a
                href="<?php echo esc_url($graceart_buy_url); ?>"
                data-quantity="1"
                data-product_id="<?php echo esc_attr($product->get_id()); ?>"
                data-product_sku="<?php echo esc_attr($product->get_sku()); ?>"
                class="graceart-loop-buy-button add_to_cart_button <?php echo $graceart_ajax_add ? 'ajax_add_to_cart' : ''; ?>"
            >
                <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                <?php echo esc_html($graceart_buy_label); ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
