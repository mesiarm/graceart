<?php

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart');
?>

<form class="woocommerce-cart-form cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
    <?php do_action('woocommerce_before_cart_table'); ?>

    <table class="cart-wishlist-table table">
        <thead>
            <tr>
                <th class="name" colspan="2"><?php esc_html_e('Produkt', 'graceart'); ?></th>
                <th class="price"><?php esc_html_e('Cena', 'graceart'); ?></th>
                <th class="quantity"><?php esc_html_e('Množstvo', 'graceart'); ?></th>
                <th class="subtotal"><?php esc_html_e('Spolu', 'graceart'); ?></th>
                <th class="remove"><span class="screen-reader-text"><?php esc_html_e('Odstrániť', 'graceart'); ?></span></th>
            </tr>
        </thead>
        <tbody>
            <?php do_action('woocommerce_before_cart_contents'); ?>

            <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) : ?>
                <?php
                $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
                $is_visible = $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key);
                ?>

                <?php if ($is_visible) : ?>
                    <?php
                    $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                    $product_name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
                    $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image('woocommerce_thumbnail'), $cart_item, $cart_item_key);
                    ?>
                    <tr class="woocommerce-cart-form__cart-item <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">
                        <td class="thumbnail">
                            <?php if ($product_permalink) : ?>
                                <a href="<?php echo esc_url($product_permalink); ?>"><?php echo wp_kses_post($thumbnail); ?></a>
                            <?php else : ?>
                                <?php echo wp_kses_post($thumbnail); ?>
                            <?php endif; ?>
                        </td>
                        <td class="name" data-title="<?php esc_attr_e('Produkt', 'graceart'); ?>">
                            <?php if ($product_permalink) : ?>
                                <a href="<?php echo esc_url($product_permalink); ?>"><?php echo esc_html($product_name); ?></a>
                            <?php else : ?>
                                <?php echo esc_html($product_name); ?>
                            <?php endif; ?>

                            <?php
                            do_action('woocommerce_after_cart_item_name', $cart_item, $cart_item_key);
                echo wc_get_formatted_cart_item_data($cart_item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                ?>

                            <?php if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])) : ?>
                                <?php echo wp_kses_post(apply_filters('woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__('Dostupné na objednávku', 'graceart') . '</p>', $product_id)); ?>
                            <?php endif; ?>
                        </td>
                        <td class="price" data-title="<?php esc_attr_e('Cena', 'graceart'); ?>">
                            <?php echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
                        </td>
                        <td class="quantity" data-title="<?php esc_attr_e('Množstvo', 'graceart'); ?>">
                            <div class="product-quantity">
                                <?php if ($_product->is_sold_individually()) : ?>
                                    <?php
                        $min_quantity = 1;
                                    $max_quantity = 1;
                                    ?>
                                <?php else : ?>
                                    <?php
                                    $min_quantity = 0;
                                    $max_quantity = $_product->get_max_purchase_quantity();
                                    ?>
                                <?php endif; ?>

                                <?php
                                $product_quantity = woocommerce_quantity_input([
                                    'input_name' => "cart[{$cart_item_key}][qty]",
                                    'input_value' => $cart_item['quantity'],
                                    'max_value' => $max_quantity,
                                    'min_value' => $min_quantity,
                                    'product_name' => $product_name,
                                    'classes' => ['input-text', 'qty', 'text', 'input-qty'],
                                ], $_product, false);

                echo apply_filters('woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                ?>
                            </div>
                        </td>
                        <td class="subtotal" data-title="<?php esc_attr_e('Spolu', 'graceart'); ?>">
                            <?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
                        </td>
                        <td class="remove">
                            <?php
                            echo apply_filters(
                                'woocommerce_cart_item_remove_link',
                                sprintf(
                                    '<a role="button" href="%s" class="btn remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
                                    esc_url(wc_get_cart_remove_url($cart_item_key)),
                                    esc_attr(sprintf(__('Odstrániť %s z košíka', 'graceart'), wp_strip_all_tags($product_name))),
                                    esc_attr($product_id),
                                    esc_attr($_product->get_sku()),
                                ),
                                $cart_item_key,
                            ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                ?>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php do_action('woocommerce_cart_contents'); ?>
            <?php do_action('woocommerce_after_cart_contents'); ?>
        </tbody>
    </table>

    <div class="row justify-content-between mb-n3">
        <div class="col-auto mb-3">
            <?php if (wc_coupons_enabled()) : ?>
                <div class="cart-coupon">
                    <label for="coupon_code" class="screen-reader-text"><?php esc_html_e('Kupón', 'graceart'); ?></label>
                    <input type="text" name="coupon_code" id="coupon_code" value="" placeholder="<?php esc_attr_e('Zadajte kód kupónu', 'graceart'); ?>">
                    <button type="submit" class="btn" name="apply_coupon" value="<?php esc_attr_e('Použiť kupón', 'graceart'); ?>"><i class="fas fa-gift"></i></button>
                    <?php do_action('woocommerce_cart_coupon'); ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-auto">
            <a class="btn btn-light btn-hover-dark mr-3 mb-3" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"><?php esc_html_e('Pokračovať v nákupe', 'graceart'); ?></a>
            <button type="submit" class="btn btn-dark btn-outline-hover-dark mb-3" name="update_cart" value="<?php esc_attr_e('Aktualizovať košík', 'graceart'); ?>"><?php esc_html_e('Aktualizovať košík', 'graceart'); ?></button>
            <?php do_action('woocommerce_cart_actions'); ?>
            <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
        </div>
    </div>

    <?php do_action('woocommerce_after_cart_table'); ?>
</form>

<?php do_action('woocommerce_before_cart_collaterals'); ?>

<div class="cart-collaterals">
    <?php do_action('woocommerce_cart_collaterals'); ?>
</div>

<?php do_action('woocommerce_after_cart'); ?>
