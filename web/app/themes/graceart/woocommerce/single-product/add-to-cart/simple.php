<?php

defined('ABSPATH') || exit;

global $product;

if (! $product instanceof WC_Product || ! $product->is_purchasable()) :
    return;
endif;

echo wc_get_stock_html($product); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>

<?php if ($product->is_in_stock()) : ?>
    <?php do_action('woocommerce_before_add_to_cart_form'); ?>

    <form class="cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype="multipart/form-data">
        <?php do_action('woocommerce_before_add_to_cart_button'); ?>

        <div class="product-variations">
            <table>
                <tbody>
                    <tr>
                        <td class="label"><span><?php esc_html_e('Množstvo', 'graceart'); ?></span></td>
                        <td class="value">
                            <div class="product-quantity">
                                <?php do_action('woocommerce_before_add_to_cart_quantity'); ?>

                                <span class="qty-btn minus"><i class="ti-minus"></i></span>
                                <input
                                    type="number"
                                    id="<?php echo esc_attr(uniqid('quantity_')); ?>"
                                    class="input-text qty text input-qty"
                                    name="quantity"
                                    value="<?php echo esc_attr(isset($_POST['quantity']) ? wc_stock_amount(wp_unslash($_POST['quantity'])) : $product->get_min_purchase_quantity()); // phpcs:ignore WordPress.Security.NonceVerification.Missing?>"
                                    aria-label="<?php esc_attr_e('Množstvo produktu', 'graceart'); ?>"
                                    min="<?php echo esc_attr($product->get_min_purchase_quantity()); ?>"
                                    max="<?php echo esc_attr(0 < $product->get_max_purchase_quantity() ? $product->get_max_purchase_quantity() : ''); ?>"
                                    step="1"
                                    inputmode="numeric"
                                >
                                <span class="qty-btn plus"><i class="ti-plus"></i></span>

                                <?php do_action('woocommerce_after_add_to_cart_quantity'); ?>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

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
