<?php

defined('ABSPATH') || exit;

$formatted_destination = isset($formatted_destination) ? $formatted_destination : WC()->countries->get_formatted_address($package['destination'], ', ');
$has_calculated_shipping = ! empty($has_calculated_shipping);
$show_shipping_calculator = ! empty($show_shipping_calculator);
$calculator_text = '';
?>

<tr class="woocommerce-shipping-totals shipping">
    <th><?php echo esc_html($package_name ?: __('Doprava', 'graceart')); ?></th>
    <td data-title="<?php echo esc_attr($package_name ?: __('Doprava', 'graceart')); ?>">
        <?php if (! empty($available_methods) && is_array($available_methods)) : ?>
            <ul id="shipping_method" class="woocommerce-shipping-methods">
                <?php foreach ($available_methods as $method) : ?>
                    <li>
                        <?php if (count($available_methods) > 1) : ?>
                            <?php printf('<input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" %4$s>', esc_attr($index), esc_attr(sanitize_title($method->id)), esc_attr($method->id), checked($method->id, $chosen_method, false)); ?>
                        <?php else : ?>
                            <?php printf('<input type="hidden" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method">', esc_attr($index), esc_attr(sanitize_title($method->id)), esc_attr($method->id)); ?>
                        <?php endif; ?>

                        <?php printf('<label for="shipping_method_%1$s_%2$s">%3$s</label>', esc_attr($index), esc_attr(sanitize_title($method->id)), wp_kses_post(wc_cart_totals_shipping_method_label($method))); ?>
                        <?php do_action('woocommerce_after_shipping_rate', $method, $index); ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if (is_cart()) : ?>
                <p class="woocommerce-shipping-destination">
                    <?php if ($formatted_destination) : ?>
                        <?php printf(esc_html__('Doručenie na adresu %s.', 'graceart') . ' ', '<strong>' . esc_html($formatted_destination) . '</strong>'); ?>
                        <?php $calculator_text = esc_html__('Zmeniť adresu', 'graceart'); ?>
                    <?php else : ?>
                        <?php echo wp_kses_post(apply_filters('woocommerce_shipping_estimate_html', __('Možnosti dopravy sa aktualizujú počas pokladne.', 'graceart'))); ?>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        <?php elseif (! $has_calculated_shipping || ! $formatted_destination) : ?>
            <?php if (is_cart() && 'no' === get_option('woocommerce_enable_shipping_calc')) : ?>
                <?php echo wp_kses_post(apply_filters('woocommerce_shipping_not_enabled_on_cart_html', __('Cena dopravy sa vypočíta v pokladni.', 'graceart'))); ?>
            <?php else : ?>
                <?php echo wp_kses_post(apply_filters('woocommerce_shipping_may_be_available_html', __('Zadajte adresu pre zobrazenie možností dopravy.', 'graceart'))); ?>
            <?php endif; ?>
        <?php elseif (! is_cart()) : ?>
            <?php echo wp_kses_post(apply_filters('woocommerce_no_shipping_available_html', __('Nie sú dostupné žiadne možnosti dopravy. Skontrolujte zadanú adresu alebo nás kontaktujte.', 'graceart'))); ?>
        <?php else : ?>
            <?php
            echo wp_kses_post(apply_filters(
                'woocommerce_cart_no_shipping_available_html',
                sprintf(esc_html__('Pre adresu %s sa nenašli žiadne možnosti dopravy.', 'graceart') . ' ', '<strong>' . esc_html($formatted_destination) . '</strong>'),
                $formatted_destination,
            ));
            $calculator_text = esc_html__('Zadať inú adresu', 'graceart');
            ?>
        <?php endif; ?>

        <?php if ($show_package_details) : ?>
            <?php echo '<p class="woocommerce-shipping-contents"><small>' . esc_html($package_details) . '</small></p>'; ?>
        <?php endif; ?>

        <?php if ($show_shipping_calculator) : ?>
            <?php woocommerce_shipping_calculator($calculator_text); ?>
        <?php endif; ?>
    </td>
</tr>
