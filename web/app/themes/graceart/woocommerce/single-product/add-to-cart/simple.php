<?php

defined('ABSPATH') || exit;

global $product;

if (! $product instanceof WC_Product || ! $product->is_purchasable()) :
    return;
endif;

// Stock is already stated in the "Dostupnosť" line of the product summary.
?>

<?php if ($product->is_in_stock()) : ?>
    <?php do_action('woocommerce_before_add_to_cart_form'); ?>

    <form class="cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype="multipart/form-data">
        <?php do_action('woocommerce_before_add_to_cart_button'); ?>

        <input type="hidden" name="quantity" value="<?php echo esc_attr($product->get_min_purchase_quantity()); ?>">

        <div class="product-buttons">
            <?php echo wp_kses_post(graceartWishlistButton($product)); ?>

            <button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="single_add_to_cart_button button alt btn btn-dark btn-hover-primary">
                <i class="fas fa-shopping-cart"></i>
                <?php echo esc_html($product->single_add_to_cart_text()); ?>
            </button>
        </div>

        <?php do_action('woocommerce_after_add_to_cart_button'); ?>
    </form>

    <?php do_action('woocommerce_after_add_to_cart_form'); ?>
<?php endif; ?>
