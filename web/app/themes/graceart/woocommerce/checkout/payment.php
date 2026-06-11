<?php

defined('ABSPATH') || exit;

if (! wp_doing_ajax()) :
    do_action('woocommerce_review_order_before_payment');
endif;
?>

<div class="col-lg-6 order-lg-1 learts-mb-30">
    <div id="payment" class="order-payment woocommerce-checkout-payment">
        <?php if (WC()->cart && WC()->cart->needs_payment()) : ?>
            <div class="payment-method">
                <ul class="wc_payment_methods payment_methods methods accordion" id="paymentMethod">
                    <?php if (! empty($available_gateways)) : ?>
                        <?php foreach ($available_gateways as $gateway) : ?>
                            <?php wc_get_template('checkout/payment-method.php', ['gateway' => $gateway]); ?>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <li>
                            <?php wc_print_notice(apply_filters('woocommerce_no_available_payment_methods_message', WC()->customer->get_billing_country() ? __('Momentálne nie sú dostupné žiadne platobné metódy. Kontaktujte nás, prosím.', 'graceart') : __('Vyplňte údaje vyššie, aby sa zobrazili dostupné platobné metódy.', 'graceart')), 'notice'); ?>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="text-center form-row place-order">
            <noscript>
                <?php esc_html_e('Keďže váš prehliadač nepodporuje JavaScript alebo je vypnutý, pred odoslaním objednávky aktualizujte súhrn.', 'graceart'); ?>
                <br>
                <button type="submit" class="button alt btn btn-dark btn-outline-hover-dark" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e('Aktualizovať súhrn', 'graceart'); ?>"><?php esc_html_e('Aktualizovať súhrn', 'graceart'); ?></button>
            </noscript>

            <?php wc_get_template('checkout/terms.php'); ?>
            <?php do_action('woocommerce_review_order_before_submit'); ?>

            <?php echo apply_filters('woocommerce_order_button_html', '<button type="submit" class="button alt btn btn-dark btn-outline-hover-dark" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr($order_button_text) . '" data-value="' . esc_attr($order_button_text) . '">' . esc_html($order_button_text) . '</button>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>

            <?php do_action('woocommerce_review_order_after_submit'); ?>
            <?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>
        </div>
    </div>
</div>

<?php
if (! wp_doing_ajax()) :
    do_action('woocommerce_review_order_after_payment');
endif;
