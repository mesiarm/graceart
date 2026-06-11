<?php

defined('ABSPATH') || exit;

if (! wc_coupons_enabled()) :
    return;
endif;
?>

<div class="woocommerce-form-coupon-toggle">
    <?php
    wc_print_notice(
        apply_filters(
            'woocommerce_checkout_coupon_message',
            esc_html__('Máte kupón?', 'graceart') . ' <a href="#" role="button" aria-label="' . esc_attr__('Zadať kód kupónu', 'graceart') . '" aria-controls="woocommerce-checkout-form-coupon" aria-expanded="false" class="showcoupon">' . esc_html__('Kliknite sem a zadajte kód', 'graceart') . '</a>',
        ),
        'notice',
    );
?>
</div>

<form class="checkout_coupon woocommerce-form-coupon" method="post" style="display:none" id="woocommerce-checkout-form-coupon">
    <p class="form-row form-row-first">
        <label for="coupon_code" class="screen-reader-text"><?php esc_html_e('Kupón', 'graceart'); ?></label>
        <input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e('Kód kupónu', 'graceart'); ?>" id="coupon_code" value="">
    </p>

    <p class="form-row form-row-last">
        <button type="submit" class="button btn btn-dark btn-outline-hover-dark" name="apply_coupon" value="<?php esc_attr_e('Použiť kupón', 'graceart'); ?>"><?php esc_html_e('Použiť kupón', 'graceart'); ?></button>
    </p>

    <div class="clear"></div>
</form>
