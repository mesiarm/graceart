<?php
/**
 * "Thank you" notice at the top of the order-received page, in the site's
 * notice style. Also shown above the e-mail verification and login forms,
 * where $order is false.
 *
 * @see woocommerce/templates/checkout/order-received.php (version 8.1.0)
 *
 * @var WC_Order|false $order
 */

defined('ABSPATH') || exit;

$graceart_message = apply_filters(
    'woocommerce_thankyou_order_received_text',
    esc_html__('Ďakujeme. Vaša objednávka bola prijatá.', 'graceart'),
    $order
);

$graceart_email = $order instanceof WC_Order ? $order->get_billing_email() : '';
?>

<div class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received graceart-woo-notice graceart-woo-notice--success" role="status">
    <span class="graceart-woo-notice__icon"><i class="far fa-check-circle"></i></span>
    <div class="graceart-woo-notice__content">
        <p class="graceart-order__received">
            <strong><?php echo $graceart_message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
            <?php if ($graceart_email !== '') : ?>
                <span class="graceart-order__received-note">
                    <?php
                    printf(
                        /* translators: %s: customer e-mail address */
                        esc_html__('Potvrdenie s podrobnosťami sme poslali na %s.', 'graceart'),
                        '<strong>' . esc_html($graceart_email) . '</strong>'
                    );
                    ?>
                </span>
            <?php endif; ?>
        </p>
    </div>
</div>
