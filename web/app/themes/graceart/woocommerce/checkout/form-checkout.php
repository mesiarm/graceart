<?php

defined('ABSPATH') || exit;

do_action('woocommerce_before_checkout_form', $checkout);

if (! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in()) :
    echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('Pre dokončenie objednávky sa musíte prihlásiť.', 'graceart')));

    return;
endif;
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data" aria-label="<?php esc_attr_e('Pokladňa', 'graceart'); ?>">
    <?php if ($checkout->get_checkout_fields()) : ?>
        <?php do_action('woocommerce_checkout_before_customer_details'); ?>

        <div id="customer_details">
            <?php do_action('woocommerce_checkout_billing'); ?>
            <?php do_action('woocommerce_checkout_shipping'); ?>
        </div>

        <?php do_action('woocommerce_checkout_after_customer_details'); ?>
    <?php endif; ?>

    <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>

    <div class="section-title2 text-center">
        <h2 class="title" id="order_review_heading"><?php esc_html_e('Vaša objednávka', 'graceart'); ?></h2>
    </div>

    <?php do_action('woocommerce_checkout_before_order_review'); ?>

    <div id="order_review" class="woocommerce-checkout-review-order row learts-mb-n30">
        <?php do_action('woocommerce_checkout_order_review'); ?>
    </div>

    <?php do_action('woocommerce_checkout_after_order_review'); ?>
</form>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
