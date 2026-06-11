<?php

defined('ABSPATH') || exit;

$collapse_id = 'payment_method_panel_' . sanitize_html_class($gateway->id);
?>

<li class="card wc_payment_method payment_method_<?php echo esc_attr($gateway->id); ?> <?php echo $gateway->chosen ? 'active' : ''; ?>">
    <div class="card-header">
        <input id="payment_method_<?php echo esc_attr($gateway->id); ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr($gateway->id); ?>" <?php checked($gateway->chosen, true); ?> data-order_button_text="<?php echo esc_attr($gateway->order_button_text); ?>">
        <label for="payment_method_<?php echo esc_attr($gateway->id); ?>" data-bs-toggle="collapse" data-bs-target="#<?php echo esc_attr($collapse_id); ?>">
            <?php echo wp_kses_post($gateway->get_title()); ?>
            <?php echo wp_kses_post($gateway->get_icon()); ?>
        </label>
    </div>

    <?php if ($gateway->has_fields() || $gateway->get_description()) : ?>
        <div id="<?php echo esc_attr($collapse_id); ?>" class="collapse <?php echo $gateway->chosen ? 'show' : ''; ?>" data-bs-parent="#paymentMethod">
            <div class="card-body payment_box payment_method_<?php echo esc_attr($gateway->id); ?>">
                <?php $gateway->payment_fields(); ?>
            </div>
        </div>
    <?php endif; ?>
</li>
