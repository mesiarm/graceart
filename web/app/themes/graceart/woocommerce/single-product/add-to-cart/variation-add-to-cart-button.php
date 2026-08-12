<?php

defined('ABSPATH') || exit;

global $product;
?>
<div class="woocommerce-variation-add-to-cart variations_button">
    <?php do_action('woocommerce_before_add_to_cart_button'); ?>

    <input type="hidden" name="quantity" value="<?php echo esc_attr($product->get_min_purchase_quantity()); ?>">

    <div class="product-buttons">
        <?php echo wp_kses_post(graceartWishlistButton($product)); ?>

        <button type="submit" class="single_add_to_cart_button button alt btn btn-dark btn-hover-primary">
            <i class="fas fa-shopping-cart"></i>
            <?php echo esc_html($product->single_add_to_cart_text()); ?>
        </button>
    </div>

    <?php do_action('woocommerce_after_add_to_cart_button'); ?>

    <input type="hidden" name="add-to-cart" value="<?php echo absint($product->get_id()); ?>">
    <input type="hidden" name="product_id" value="<?php echo absint($product->get_id()); ?>">
    <input type="hidden" name="variation_id" class="variation_id" value="0">
</div>
