<?php

defined('ABSPATH') || exit;

if (! $catalog_orderby_options) {
    return;
}
?>

<form class="woocommerce-ordering" method="get">
    <label for="orderby"><?php esc_html_e('Zoradiť podľa', 'graceart'); ?></label>
    <select name="orderby" class="orderby nice-select" id="orderby" aria-label="<?php esc_attr_e('Zoradenie produktov', 'graceart'); ?>">
        <?php foreach ($catalog_orderby_options as $id => $name) : ?>
            <option value="<?php echo esc_attr($id); ?>" <?php selected($orderby, $id); ?>><?php echo esc_html($name); ?></option>
        <?php endforeach; ?>
    </select>
    <input type="hidden" name="paged" value="1">
    <?php wc_query_string_form_fields(null, ['orderby', 'submit', 'paged', 'product-page']); ?>
</form>
