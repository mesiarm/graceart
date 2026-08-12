<?php

defined('ABSPATH') || exit;

global $product;

$attribute_keys = array_keys($attributes);

// Sold-out variants are hidden; ones on backorder stay, they can still be ordered.
if (is_array($available_variations) && $available_variations) {
    $available_variations = graceartOfferedVariations($available_variations);
}

$variations_json = wp_json_encode($available_variations);
$variations_attr = function_exists('wc_esc_json') ? wc_esc_json($variations_json) : _wp_specialchars($variations_json, ENT_QUOTES, 'UTF-8', true);

do_action('woocommerce_before_add_to_cart_form');
?>

<form class="variations_form cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype="multipart/form-data" data-product_id="<?php echo absint($product->get_id()); ?>" data-product_variations="<?php echo $variations_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>">
    <?php do_action('woocommerce_before_variations_form'); ?>

    <?php if (empty($available_variations) && false !== $available_variations) : ?>
        <p class="stock out-of-stock"><?php echo esc_html(apply_filters('woocommerce_out_of_stock_message', __('Tento produkt je momentálne vypredaný a nedostupný.', 'graceart'))); ?></p>
    <?php else : ?>
        <table class="variations graceart-variations-table" cellspacing="0" role="presentation">
            <tbody>
                <?php foreach ($attributes as $attribute_name => $options) : ?>
                    <tr>
                        <th class="label"><label for="<?php echo esc_attr(sanitize_title($attribute_name)); ?>"><?php echo wc_attribute_label($attribute_name); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?></label></th>
                        <td class="value">
                            <?php
                                wc_dropdown_variation_attribute_options([
                                    'options' => $options,
                                    'attribute' => $attribute_name,
                                    'product' => $product,
                                ]);
                    ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php $graceart_selected_variation = graceartResolveSelectedVariationData($product); ?>
        <div class="graceart-variation-options">
            <?php
                        foreach ($available_variations as $index => $variation) :
                            $variation_id = (int) $variation['variation_id'];
                            $is_selected = $graceart_selected_variation
                                ? (int) $graceart_selected_variation['variation_id'] === $variation_id
                                : $index === 0;

                            $meta_parts = [];
                            foreach ($variation['attributes'] as $attribute_key => $attribute_value) {
                                if ($attribute_value === '') {
                                    continue;
                                }

                                $taxonomy = str_replace('attribute_', '', $attribute_key);

                                if (taxonomy_exists($taxonomy)) {
                                    $term = get_term_by('slug', $attribute_value, $taxonomy);
                                    $meta_parts[] = $term instanceof WP_Term ? $term->name : $attribute_value;
                                } else {
                                    $meta_parts[] = $attribute_value;
                                }
                            }

                            // In stock the option shows a plain "(Skladom)" — the count belongs
                            // in the "Dostupnosť" row — while backorders carry their lead time.
                            $variation_product = wc_get_product($variation_id);
                            $variation_availability = '';
                            $variation_stock = ['label' => '', 'in_stock' => false];

                            if ($variation_product instanceof WC_Product) {
                                $variation_availability = graceartAvailabilityText($variation_product);
                                $variation_stock = graceartAvailabilityShortLabel($variation_product);

                                if (! $variation_stock['in_stock']) {
                                    $variation_stock['label'] = $variation_availability;
                                }
                            }

                            $price_html = $variation['price_html'] ? $variation['price_html'] : wp_kses_post(wc_price($variation['display_price']));
                            ?>
                <label class="graceart-variation-option">
                    <input
                        type="radio"
                        class="graceart-variation-radio"
                        name="graceart_variation_<?php echo esc_attr($product->get_id()); ?>"
                        value="<?php echo esc_attr($variation_id); ?>"
                        data-attributes="<?php echo esc_attr(wp_json_encode($variation['attributes'])); ?>"
                        data-availability="<?php echo esc_attr($variation_availability); ?>"
                        data-price-html="<?php echo esc_attr($price_html); ?>"
                        <?php checked($is_selected); ?>
                    >
                    <span class="graceart-variation-option__content">
                        <?php if ($meta_parts) : ?>
                            <span class="graceart-variation-option__meta">
                                <?php echo esc_html(implode(', ', $meta_parts)); ?>
                                <?php if ($variation_stock['label']) : ?>
                                    <span class="graceart-variation-option__stock<?php echo $variation_stock['in_stock'] ? ' is-in-stock' : ''; ?>">
                                        (<?php echo esc_html($variation_stock['label']); ?>)
                                    </span>
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>

        <div class="reset_variations_alert screen-reader-text" role="alert" aria-live="polite" aria-relevant="all"></div>
        <?php
        if (\Automattic\WooCommerce\Internal\VariationGallery\Package::is_enabled()) :
            ?>
            <script type="text/template" class="wc-product-gallery-default-template"><?php echo wc_get_product_gallery_html($product); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?></script>
            <?php
        endif;
?>
        <?php do_action('woocommerce_after_variations_table'); ?>

        <div class="single_variation_wrap">
            <?php
        do_action('woocommerce_before_single_variation');
do_action('woocommerce_single_variation');
do_action('woocommerce_after_single_variation');
?>
        </div>
    <?php endif; ?>

    <?php do_action('woocommerce_after_variations_form'); ?>
</form>

<?php
do_action('woocommerce_after_add_to_cart_form');
