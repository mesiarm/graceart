<?php
/**
 * Order-received ("thank you") page.
 *
 * Laid out like the cart: a product list with the totals under it, the
 * addresses beside it. The totals leave the subtotal out — with no coupons
 * it only repeats the item prices. WooCommerce's own details table is taken
 * off the woocommerce_thankyou hook in inc/woocommerce/checkout.php; the
 * gateway hooks (bank transfer details) still print above the columns.
 *
 * @see woocommerce/templates/checkout/thankyou.php (version 8.1.0)
 *
 * @var WC_Order|false $order
 */

defined('ABSPATH') || exit;
?>

<div class="woocommerce-order graceart-order">

    <?php if (! $order) : ?>

        <?php wc_get_template('checkout/order-received.php', ['order' => false]); ?>

    <?php else : ?>

        <?php do_action('woocommerce_before_thankyou', $order->get_id()); ?>

        <?php if ($order->has_status('failed')) : ?>

            <div class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed graceart-woo-notice graceart-woo-notice--error" role="alert">
                <span class="graceart-woo-notice__icon"><i class="far fa-times-circle"></i></span>
                <div class="graceart-woo-notice__content">
                    <?php esc_html_e('Ľutujeme, objednávku sa nepodarilo spracovať – banka alebo poskytovateľ platby transakciu zamietli. Skúste prosím platbu zopakovať.', 'graceart'); ?>
                </div>
            </div>

            <p class="graceart-order__actions woocommerce-thankyou-order-failed-actions">
                <a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" class="graceart-order__button button pay"><?php esc_html_e('Zaplatiť', 'graceart'); ?></a>
                <?php if (is_user_logged_in()) : ?>
                    <a href="<?php echo esc_url(graceartMyAccountUrl()); ?>" class="graceart-order__button button pay"><?php esc_html_e('Môj účet', 'graceart'); ?></a>
                <?php endif; ?>
            </p>

        <?php else : ?>

            <?php wc_get_template('checkout/order-received.php', ['order' => $order]); ?>

            <ul class="woocommerce-order-overview graceart-order__overview">
                <li class="woocommerce-order-overview__order order">
                    <span class="graceart-order__overview-label"><?php esc_html_e('Číslo objednávky', 'graceart'); ?></span>
                    <strong><?php echo esc_html($order->get_order_number()); ?></strong>
                </li>
                <li class="woocommerce-order-overview__date date">
                    <span class="graceart-order__overview-label"><?php esc_html_e('Dátum', 'graceart'); ?></span>
                    <strong><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></strong>
                </li>
                <li class="woocommerce-order-overview__total total">
                    <span class="graceart-order__overview-label"><?php esc_html_e('Spolu', 'graceart'); ?></span>
                    <strong><?php echo wp_kses_post($order->get_formatted_order_total()); ?></strong>
                </li>
                <?php if ($order->get_payment_method_title()) : ?>
                    <li class="woocommerce-order-overview__payment-method method">
                        <span class="graceart-order__overview-label"><?php esc_html_e('Spôsob platby', 'graceart'); ?></span>
                        <strong><?php echo wp_kses_post($order->get_payment_method_title()); ?></strong>
                    </li>
                <?php endif; ?>
                <?php $graceart_availability = graceartOrderAvailabilityText($order); ?>
                <?php if ($graceart_availability !== '') : ?>
                    <li class="graceart-order__overview-availability">
                        <span class="graceart-order__overview-label"><?php esc_html_e('Dostupnosť', 'graceart'); ?></span>
                        <strong><?php echo esc_html($graceart_availability); ?></strong>
                    </li>
                <?php endif; ?>
            </ul>

            <?php
            // Payment instructions (bank transfer details) and anything else
            // plugins add to the page.
            ob_start();
            do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id());
            do_action('woocommerce_thankyou', $order->get_id());
            $graceart_hooked = trim((string) ob_get_clean());

            if ($graceart_hooked !== '') :
                ?>
                <div class="graceart-order__instructions">
                    <?php echo $graceart_hooked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
            <?php endif; ?>

            <?php
            $graceart_items = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));
            $graceart_totals = $order->get_order_item_totals();

            // The subtotal only repeats the item prices; the payment method
            // is in the overview strip.
            unset($graceart_totals['cart_subtotal'], $graceart_totals['payment_method']);

            $graceart_total_labels = [
                'shipping' => __('Doprava', 'graceart'),
                'discount' => __('Zľava', 'graceart'),
                'order_total' => __('Spolu', 'graceart'),
            ];

            // Guests see their own guest order; customers only their own.
            $graceart_show_customer = $order->get_user_id() === get_current_user_id();

            $graceart_actions = array_filter(
                wc_get_account_orders_actions($order),
                // No "view" (this is the view), and no button without a link
                // (the invoice link is empty for guests).
                static fn (array $action, string $key): bool => $key !== 'view' && ! empty($action['url']),
                ARRAY_FILTER_USE_BOTH
            );
            ?>

            <div class="row learts-mb-n40">
                <div class="col-lg-8 col-12 learts-mb-40">
                    <section class="woocommerce-order-details graceart-order__details">
                        <?php do_action('woocommerce_order_details_before_order_table', $order); ?>

                        <h2 class="woocommerce-order-details__title graceart-order__title"><?php esc_html_e('Podrobnosti objednávky', 'graceart'); ?></h2>

                        <div class="graceart-order__head">
                            <span><?php esc_html_e('Produkt', 'graceart'); ?></span>
                            <span><?php esc_html_e('Spolu', 'graceart'); ?></span>
                        </div>

                        <ul class="graceart-order__items">
                            <?php foreach ($graceart_items as $graceart_item_id => $graceart_item) : ?>
                                <?php
                                if (! apply_filters('woocommerce_order_item_visible', true, $graceart_item)) {
                                    continue;
                                }

                                $graceart_product = $graceart_item->get_product();
                                $graceart_visible = $graceart_product && $graceart_product->is_visible();
                                $graceart_permalink = apply_filters('woocommerce_order_item_permalink', $graceart_visible ? $graceart_product->get_permalink($graceart_item) : '', $graceart_item, $order);
                                // A variation's item name is "Product - Size"; show the plain
                                // product name and list the attributes underneath instead,
                                // the same way the checkout summary does.
                                $graceart_is_variation = $graceart_product && $graceart_product->is_type('variation');
                                $graceart_item_name = $graceart_is_variation ? $graceart_product->get_title() : $graceart_item->get_name();
                                $graceart_name = apply_filters(
                                    'woocommerce_order_item_name',
                                    $graceart_permalink ? sprintf('<a href="%s">%s</a>', esc_url($graceart_permalink), esc_html($graceart_item_name)) : esc_html($graceart_item_name),
                                    $graceart_item,
                                    $graceart_visible
                                );
                                // Attributes that are part of the item name are normally hidden
                                // from the meta; the name no longer carries them, so show them all.
                                add_filter('woocommerce_is_attribute_in_product_name', '__return_false');
                                $graceart_meta = wc_display_item_meta($graceart_item, ['echo' => false]);
                                remove_filter('woocommerce_is_attribute_in_product_name', '__return_false');
                                $graceart_unit = wc_price($order->get_item_subtotal($graceart_item, get_option('woocommerce_tax_display_cart') === 'incl'), ['currency' => $order->get_currency()]);
                                ?>
                                <li class="<?php echo esc_attr(apply_filters('woocommerce_order_item_class', 'woocommerce-table__line-item order_item graceart-order__item', $graceart_item, $order)); ?>">
                                    <div class="graceart-order__item-thumb">
                                        <?php if ($graceart_product && $graceart_permalink) : ?>
                                            <a href="<?php echo esc_url($graceart_permalink); ?>"><?php echo $graceart_product->get_image('thumbnail'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
                                        <?php elseif ($graceart_product) : ?>
                                            <?php echo $graceart_product->get_image('thumbnail'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="graceart-order__item-info">
                                        <span class="graceart-order__item-name"><?php echo wp_kses_post($graceart_name); ?></span>
                                        <?php /* Quantity and unit price as in the checkout summary: "5 ks" under the name, "55 € / kus" below. */ ?>
                                        <span class="graceart-order__item-qty"><?php echo esc_html(sprintf(__('%d ks', 'graceart'), $graceart_item->get_quantity())); ?></span>
                                        <?php if ($graceart_meta) : ?>
                                            <div class="graceart-order__item-meta"><?php echo wp_kses_post($graceart_meta); ?></div>
                                        <?php endif; ?>
                                        <span class="graceart-order__item-unit"><?php echo wp_kses_post($graceart_unit); ?> / <?php esc_html_e('kus', 'graceart'); ?></span>
                                    </div>
                                    <div class="graceart-order__item-total">
                                        <?php echo wp_kses_post($order->get_formatted_line_subtotal($graceart_item)); ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <dl class="graceart-order__totals">
                            <?php foreach ($graceart_totals as $graceart_key => $graceart_total) : ?>
                                <div class="graceart-order__total graceart-order__total--<?php echo esc_attr($graceart_key); ?>">
                                    <dt><?php echo esc_html($graceart_total_labels[$graceart_key] ?? rtrim($graceart_total['label'], ':')); ?></dt>
                                    <dd><?php echo wp_kses_post($graceart_total['value']); ?></dd>
                                </div>
                            <?php endforeach; ?>
                        </dl>

                        <?php do_action('woocommerce_order_details_after_order_table', $order); ?>
                    </section>

                    <?php do_action('woocommerce_after_order_details', $order); ?>
                </div>

                <div class="col-lg-4 col-12 learts-mb-40">
                    <?php if ($graceart_show_customer) : ?>
                        <section class="woocommerce-customer-details graceart-order__customer">
                            <div class="graceart-order__address">
                                <h2 class="woocommerce-column__title graceart-order__subtitle"><?php esc_html_e('Fakturačná adresa', 'graceart'); ?></h2>
                                <address>
                                    <?php echo wp_kses_post($order->get_formatted_billing_address(esc_html__('Neuvedené', 'graceart'))); ?>
                                    <?php if ($order->get_billing_phone()) : ?>
                                        <p class="woocommerce-customer-details--phone"><?php echo esc_html($order->get_billing_phone()); ?></p>
                                    <?php endif; ?>
                                    <?php if ($order->get_billing_email()) : ?>
                                        <p class="woocommerce-customer-details--email"><?php echo esc_html($order->get_billing_email()); ?></p>
                                    <?php endif; ?>
                                </address>
                            </div>

                            <?php if ($order->needs_shipping_address()) : ?>
                                <div class="graceart-order__address">
                                    <h2 class="woocommerce-column__title graceart-order__subtitle"><?php esc_html_e('Dodacia adresa', 'graceart'); ?></h2>
                                    <address>
                                        <?php echo wp_kses_post($order->get_formatted_shipping_address(esc_html__('Neuvedené', 'graceart'))); ?>
                                        <?php if ($order->get_shipping_phone()) : ?>
                                            <p class="woocommerce-customer-details--phone"><?php echo esc_html($order->get_shipping_phone()); ?></p>
                                        <?php endif; ?>
                                    </address>
                                </div>
                            <?php endif; ?>

                            <?php if ($order->get_customer_note()) : ?>
                                <div class="graceart-order__address graceart-order__note">
                                    <h2 class="graceart-order__subtitle"><?php esc_html_e('Poznámka k objednávke', 'graceart'); ?></h2>
                                    <p><?php echo wp_kses(nl2br(wc_wptexturize_order_note($order->get_customer_note())), ['br' => []]); ?></p>
                                </div>
                            <?php endif; ?>

                            <?php do_action('woocommerce_order_details_after_customer_details', $order); ?>
                        </section>
                    <?php endif; ?>
                </div>
            </div>

            <p class="graceart-order__actions">
                <a href="<?php echo esc_url(graceartShopUrl()); ?>" class="graceart-order__button"><?php esc_html_e('Pokračovať v nákupe', 'graceart'); ?></a>
                <?php foreach ($graceart_actions as $graceart_key => $graceart_action) : ?>
                    <a href="<?php echo esc_url($graceart_action['url']); ?>" class="graceart-order__button woocommerce-button button <?php echo sanitize_html_class($graceart_key); ?> order-actions-button"><?php echo esc_html($graceart_action['name']); ?></a>
                <?php endforeach; ?>
            </p>

        <?php endif; ?>

    <?php endif; ?>

</div>
