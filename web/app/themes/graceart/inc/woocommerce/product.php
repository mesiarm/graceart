<?php

function graceartOrderVariationBySize(int $post_id, WP_Post $post): void
{
    if (wp_is_post_revision($post_id) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
        return;
    }

    $size = get_post_meta($post_id, 'attribute_pa_velkost', true);
    $order_by_size = ['a5' => 0, 'a4' => 1];

    if (! isset($order_by_size[$size]) || (int) $post->menu_order === $order_by_size[$size]) {
        return;
    }

    remove_action('save_post_product_variation', 'graceartOrderVariationBySize', 20);
    wp_update_post(['ID' => $post_id, 'menu_order' => $order_by_size[$size]]);
    add_action('save_post_product_variation', 'graceartOrderVariationBySize', 20, 2);

    delete_transient('wc_product_children_' . $post->post_parent);
}
add_action('save_post_product_variation', 'graceartOrderVariationBySize', 20, 2);

add_action('template_redirect', function (): void {
    if (! is_singular('product') || ! is_main_query()) {
        return;
    }

    $post_id = get_queried_object_id();

    if (! $post_id) {
        return;
    }

    $terms = wp_get_object_terms($post_id, 'product_visibility', ['fields' => 'slugs']);
    $terms = is_wp_error($terms) ? [] : $terms;

    $is_hidden = in_array('exclude-from-catalog', $terms, true) && in_array('exclude-from-search', $terms, true);

    if (! $is_hidden) {
        return;
    }

    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    nocache_headers();
}, 5);

add_action('template_redirect', function (): void {
    if (! is_singular('product') || ! is_main_query() || is_admin()) {
        return;
    }

    $post_id = get_queried_object_id();

    if (! $post_id) {
        return;
    }

    $views = (int) get_post_meta($post_id, '_graceart_view_count', true);
    update_post_meta($post_id, '_graceart_view_count', $views + 1);
});

add_filter('woocommerce_product_single_add_to_cart_text', fn(): string => __('Pridať do košíka', 'graceart'));

add_filter('woocommerce_product_add_to_cart_text', function (string $text, WC_Product $product): string {
    if ($product->is_type('variable')) {
        return __('Vybrať možnosti', 'graceart');
    }

    if ($product->is_type('grouped')) {
        return __('Zobraziť produkty', 'graceart');
    }

    if (! $product->is_in_stock()) {
        return __('Nie je skladom', 'graceart');
    }

    if (! $product->is_purchasable()) {
        return __('Zobraziť produkt', 'graceart');
    }

    return __('Pridať do košíka', 'graceart');
}, 10, 2);

add_filter('woocommerce_product_tabs', function (array $tabs): array {
    if (isset($tabs['description'])) {
        $tabs['description']['title'] = __('Popis', 'graceart');
    }

    if (isset($tabs['reviews'])) {
        global $product;

        $review_count = $product instanceof WC_Product ? $product->get_review_count() : 0;
        $tabs['reviews']['title'] = sprintf(__('Recenzie (%d)', 'graceart'), $review_count);
    }

    return $tabs;
}, 20);

add_filter('woocommerce_product_description_heading', fn(): string => __('Popis', 'graceart'));

add_filter('woocommerce_product_related_products_heading', fn(): string => __('Mohlo by sa vám páčiť', 'graceart'));

add_filter('woocommerce_reviews_title', function (string $title, int $count, WC_Product $product): string {
    if ($count > 0) {
        return sprintf(__('Recenzie (%d) pre %s', 'graceart'), $count, '<span>' . esc_html($product->get_name()) . '</span>');
    }

    return __('Recenzie', 'graceart');
}, 10, 3);

function graceartWishlistProductUrl(WC_Product $product): string
{
    if (function_exists('YITH_WCWL')) {
        return wp_nonce_url(
            add_query_arg('add_to_wishlist', $product->get_id(), $product->get_permalink()),
            'add_to_wishlist',
        );
    }

    return graceartWishlistUrl();
}

function graceartWishlistButton(WC_Product $product): string
{
    if (function_exists('YITH_WCWL')) {
        return '<span class="graceart-wishlist-button">' . do_shortcode('[yith_wcwl_add_to_wishlist]') . '</span>';
    }

    return sprintf(
        '<a href="%1$s" class="btn btn-icon btn-outline-body btn-hover-dark hintT-top" data-hint="%2$s" aria-label="%2$s"><i class="far fa-heart"></i></a>',
        esc_url(graceartWishlistProductUrl($product)),
        esc_attr__('Pridať do zoznamu prianí', 'graceart'),
    );
}

function graceartProductGalleryImages(WC_Product $product): array
{
    $image_ids = array_filter(array_merge(
        [$product->get_image_id()],
        $product->get_gallery_image_ids(),
    ));

    if (! $image_ids) {
        return [[
            'type' => 'image',
            'alt' => $product->get_name(),
            'thumb' => wc_placeholder_img_src('woocommerce_thumbnail'),
            'full' => wc_placeholder_img_src('woocommerce_single'),
            'large' => wc_placeholder_img_src('woocommerce_single'),
            'width' => 700,
            'height' => 1100,
        ]];
    }

    return array_map(function (int $image_id) use ($product) {
        if (wp_attachment_is('video', $image_id)) {
            return [
                'type' => 'video',
                'alt' => get_the_title($image_id) ?: $product->get_name(),
                'video_url' => wp_get_attachment_url($image_id),
                'mime' => get_post_mime_type($image_id) ?: 'video/mp4',
            ];
        }

        $full = wp_get_attachment_image_src($image_id, 'full');

        return [
            'type' => 'image',
            'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: $product->get_name(),
            'thumb' => wp_get_attachment_image_url($image_id, 'woocommerce_thumbnail'),
            'full' => $full[0] ?? wp_get_attachment_image_url($image_id, 'full'),
            'large' => wp_get_attachment_image_url($image_id, 'woocommerce_single'),
            'width' => $full[1] ?? 700,
            'height' => $full[2] ?? 1100,
        ];
    }, $image_ids);
}

function graceartProductGalleryPopupImages(array $images): string
{
    $images = array_values(array_filter($images, fn(array $image) => $image['type'] === 'image'));

    return wp_json_encode(array_map(fn(array $image) => [
        'src' => $image['full'],
        'w' => $image['width'],
        'h' => $image['height'],
    ], $images));
}

function graceartProductLoopClasses(WC_Product $product): string
{
    $classes = ['grid-item', 'col'];

    foreach (wc_get_product_term_ids($product->get_id(), 'product_cat') as $term_id) {
        $classes[] = 'cat-' . $term_id;
    }

    return implode(' ', $classes);
}

function graceartProductLoopCategoryFilters(): array
{
    if (! taxonomy_exists('product_cat')) {
        return [];
    }

    $terms = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => true,
    ]);

    if (is_wp_error($terms)) {
        return [];
    }

    return array_map(function (WP_Term $term): array {
        return [
            'label' => $term->slug === 'uncategorized' ? __('Nezaradené', 'graceart') : $term->name,
            'filter' => '.cat-' . $term->term_id,
        ];
    }, $terms);
}

function graceartProductBadgeHtml(WC_Product $product): string
{
    $badges = [];

    if (! $product->is_in_stock()) {
        $badges[] = '<span class="outofstock"><i class="far fa-frown"></i></span>';
    }

    if ($product->is_featured()) {
        $badges[] = '<span class="hot">' . esc_html__('top', 'graceart') . '</span>';
    }

    if ($product->is_on_sale()) {
        $badges[] = '<span class="onsale">' . esc_html__('zľava', 'graceart') . '</span>';
    }

    return $badges ? '<span class="product-badges">' . implode('', $badges) . '</span>' : '';
}

function graceartProductImageUrl(WC_Product $product, string $size = 'woocommerce_thumbnail'): string
{
    $image_id = $product->get_image_id();

    return $image_id ? wp_get_attachment_image_url($image_id, $size) : wc_placeholder_img_src($size);
}

function graceartProductHoverImageUrl(WC_Product $product, string $size = 'woocommerce_thumbnail'): string
{
    $gallery_ids = $product->get_gallery_image_ids();

    return $gallery_ids ? wp_get_attachment_image_url((int) $gallery_ids[0], $size) : '';
}

function graceartLeadTimeOptions(): array
{
    return [
        '3_dni' => __('3 dni', 'graceart'),
        '1_tyzden' => __('1 týždeň', 'graceart'),
        '2_tyzdne' => __('2 týždne', 'graceart'),
    ];
}

/**
 * Lead times in the genitive form used after "do" ("Na objednávku do 1 týždňa").
 */
function graceartLeadTimePhrases(): array
{
    return [
        '3_dni' => __('do 3 dní', 'graceart'),
        '1_tyzden' => __('do 1 týždňa', 'graceart'),
        '2_tyzdne' => __('do 2 týždňov', 'graceart'),
    ];
}

function graceartAvailabilityModeOptions(): array
{
    return [
        'stock' => __('Na sklade', 'graceart'),
        'backorder' => __('Na objednávku', 'graceart'),
    ];
}

add_action('woocommerce_product_options_stock_fields', function (): void {
    global $post;

    woocommerce_wp_radio([
        'id' => '_graceart_availability_mode',
        'label' => __('Dostupnosť', 'graceart'),
        'description' => __('Určuje, či sa zákazníkovi zobrazí „Na sklade“ alebo „Na objednávku“.', 'graceart'),
        'desc_tip' => true,
        'value' => get_post_meta($post->ID, '_graceart_availability_mode', true) ?: 'stock',
        'options' => graceartAvailabilityModeOptions(),
        'wrapper_class' => 'hide_if_variable',
    ]);

    woocommerce_wp_text_input([
        'id' => '_graceart_backorder_qty',
        'label' => __('Počet na objednávku', 'graceart'),
        'description' => __('Počet kusov dostupných na objednávku.', 'graceart'),
        'desc_tip' => true,
        'type' => 'number',
        'custom_attributes' => ['step' => '1', 'min' => '0'],
        'wrapper_class' => 'hide_if_variable',
    ]);

    woocommerce_wp_select([
        'id' => '_graceart_lead_time',
        'label' => __('Dodacia lehota (na objednávku)', 'graceart'),
        'description' => __('Zobrazí sa zákazníkovi pri dostupnosti „Na objednávku“.', 'graceart'),
        'desc_tip' => true,
        'options' => graceartLeadTimeOptions(),
        'wrapper_class' => 'hide_if_variable',
    ]);
    ?>
    <style>
    .graceart-inline-stock-qty {
        display: inline-block;
        margin-left: 10px;
    }
    </style>
    <script>
    jQuery(function ($) {
        function graceartToggleFields() {
            var isBackorder = $('input[name="_graceart_availability_mode"]:checked').val() === 'backorder';
            $('.form-field._graceart_backorder_qty_field, .form-field._graceart_lead_time_field').toggle(isBackorder);
        }

        if ($('#product-type').val() !== 'variable') {
            $(document.body).on('change', 'input[name="_graceart_availability_mode"]', graceartToggleFields);
            graceartToggleFields();

            // Move the native stock quantity input next to the "Na sklade" radio option.
            var $stockInput = $('#_stock'),
                $stockLabel = $('input[name="_graceart_availability_mode"][value="stock"]').closest('label'),
                $manageStock = $('#_manage_stock');

            if ($stockInput.length && $stockLabel.length) {
                var $originalStockRow = $stockInput.closest('.form-field'),
                    $wrap = $('<span class="graceart-inline-stock-qty"></span>');

                $stockInput.css({width: '70px'}).appendTo($wrap);
                $wrap.appendTo($stockLabel);
                $originalStockRow.remove();

                function graceartSyncStockVisibility() {
                    $wrap.toggle($manageStock.is(':checked'));
                }

                $(document.body).on('change', '#_manage_stock', graceartSyncStockVisibility);
                graceartSyncStockVisibility();
            }

            // Redundant with the "Dostupnosť" toggle above.
            $('.form-field._backorders_field, .form-field._low_stock_amount_field').remove();
        }

        // Same cleanup, per variation, whenever variation rows are (re)loaded.
        $('#woocommerce-product-data, #variable_product_options').on('woocommerce_variations_loaded woocommerce_variations_added', function () {
            $('.woocommerce_variation').each(function () {
                var $variation = $(this);

                if ($variation.data('graceart-processed')) {
                    return;
                }

                $variation.data('graceart-processed', true);

                function graceartToggleVariationFields() {
                    var isBackorder = $variation.find('input[name^="_graceart_availability_mode["]:checked').val() === 'backorder';
                    $variation.find('.form-field[class*="_graceart_backorder_qty"], .form-field[class*="_graceart_lead_time"]').toggle(isBackorder);
                }

                $variation.on('change', 'input[name^="_graceart_availability_mode["]', graceartToggleVariationFields);
                graceartToggleVariationFields();

                var $vStockInput = $variation.find('input[name^="variable_stock["]'),
                    $vStockLabel = $variation.find('input[name^="_graceart_availability_mode["][value="stock"]').closest('label'),
                    $vManageStock = $variation.find('input[name^="variable_manage_stock["]');

                if ($vStockInput.length && $vStockLabel.length) {
                    var $vWrap = $('<span class="graceart-inline-stock-qty"></span>');

                    $vStockInput.closest('.form-row').remove();
                    $vStockInput.css({width: '70px'}).appendTo($vWrap);
                    $vWrap.appendTo($vStockLabel);

                    function graceartSyncVariationStockVisibility() {
                        $vWrap.toggle($vManageStock.length ? $vManageStock.is(':checked') : true);
                    }

                    $variation.on('change', 'input[name^="variable_manage_stock["]', graceartSyncVariationStockVisibility);
                    graceartSyncVariationStockVisibility();
                }

                $variation.find('.form-row:has(select[name^="variable_backorders["]), .form-row:has(input[name^="variable_low_stock_amount["])').remove();
            });
        });

        $('#woocommerce-product-data').trigger('woocommerce_variations_loaded');
    });
    </script>
    <?php
});

add_action('woocommerce_process_product_meta', function (int $post_id): void {
    if (isset($_POST['_graceart_availability_mode'])) {
        $mode = sanitize_text_field(wp_unslash($_POST['_graceart_availability_mode']));

        if (array_key_exists($mode, graceartAvailabilityModeOptions())) {
            update_post_meta($post_id, '_graceart_availability_mode', $mode);
        }
    }

    if (isset($_POST['_graceart_backorder_qty'])) {
        update_post_meta($post_id, '_graceart_backorder_qty', absint(wp_unslash($_POST['_graceart_backorder_qty'])));
    }

    if (! isset($_POST['_graceart_lead_time'])) {
        return;
    }

    $lead_time = sanitize_text_field(wp_unslash($_POST['_graceart_lead_time']));

    if (array_key_exists($lead_time, graceartLeadTimeOptions())) {
        update_post_meta($post_id, '_graceart_lead_time', $lead_time);
    }
});

add_action('woocommerce_product_after_variable_attributes', function (int $loop, array $variation_data, WP_Post $variation): void {
    $mode = get_post_meta($variation->ID, '_graceart_availability_mode', true) ?: 'stock';
    $backorder_qty = get_post_meta($variation->ID, '_graceart_backorder_qty', true);
    $lead_time = get_post_meta($variation->ID, '_graceart_lead_time', true) ?: '3_dni';
    ?>
    <p class="form-row form-row-full">
        <strong><?php esc_html_e('Dostupnosť', 'graceart'); ?></strong>
    </p>
    <?php
    woocommerce_wp_radio([
        'id' => "_graceart_availability_mode{$loop}",
        'name' => "_graceart_availability_mode[{$loop}]",
        'value' => $mode,
        'options' => graceartAvailabilityModeOptions(),
        'wrapper_class' => 'form-row form-row-full',
    ]);

    woocommerce_wp_text_input([
        'id' => "_graceart_backorder_qty{$loop}",
        'name' => "_graceart_backorder_qty[{$loop}]",
        'label' => __('Počet na objednávku', 'graceart'),
        'value' => $backorder_qty,
        'type' => 'number',
        'custom_attributes' => ['step' => '1', 'min' => '0'],
        'wrapper_class' => 'form-row form-row-first',
    ]);

    woocommerce_wp_select([
        'id' => "_graceart_lead_time{$loop}",
        'name' => "_graceart_lead_time[{$loop}]",
        'label' => __('Dodacia lehota', 'graceart'),
        'value' => $lead_time,
        'options' => graceartLeadTimeOptions(),
        'wrapper_class' => 'form-row form-row-last',
    ]);
}, 10, 3);

add_action('woocommerce_save_product_variation', function (int $variation_id, int $loop): void {
    if (isset($_POST['_graceart_availability_mode'][$loop])) {
        $mode = sanitize_text_field(wp_unslash($_POST['_graceart_availability_mode'][$loop]));

        if (array_key_exists($mode, graceartAvailabilityModeOptions())) {
            update_post_meta($variation_id, '_graceart_availability_mode', $mode);
        }
    }

    if (isset($_POST['_graceart_backorder_qty'][$loop])) {
        update_post_meta($variation_id, '_graceart_backorder_qty', absint(wp_unslash($_POST['_graceart_backorder_qty'][$loop])));
    }

    if (! isset($_POST['_graceart_lead_time'][$loop])) {
        return;
    }

    $lead_time = sanitize_text_field(wp_unslash($_POST['_graceart_lead_time'][$loop]));

    if (array_key_exists($lead_time, graceartLeadTimeOptions())) {
        update_post_meta($variation_id, '_graceart_lead_time', $lead_time);
    }
}, 10, 2);

// WooCommerce's own "X na sklade" line duplicates the theme's "Dostupnosť" row,
// on simple products and in the availability_html of variations alike.
add_filter('woocommerce_get_stock_html', function (string $html): string {
    return is_admin() ? $html : '';
});

function graceartAvailabilityText(WC_Product $product): string
{
    $meta_product_id = $product->get_id();
    $mode = get_post_meta($meta_product_id, '_graceart_availability_mode', true) ?: 'stock';

    if ($mode === 'backorder') {
        $lead_time_phrases = graceartLeadTimePhrases();
        $lead_time = get_post_meta($meta_product_id, '_graceart_lead_time', true);
        $lead_time_phrase = $lead_time_phrases[$lead_time] ?? reset($lead_time_phrases);

        return sprintf(__('Na objednávku %s', 'graceart'), $lead_time_phrase);
    }

    $status = $product->get_stock_status();

    if ($status === 'outofstock') {
        return __('Nie je skladom', 'graceart');
    }

    $quantity = $product->get_stock_quantity();

    if ($product->managing_stock() && $quantity !== null) {
        return sprintf(__('Skladom %d ks', 'graceart'), $quantity);
    }

    return __('Skladom', 'graceart');
}

function graceartAvailabilityShortLabel(WC_Product $product): array
{
    $meta_product_id = $product->get_id();
    $mode = get_post_meta($meta_product_id, '_graceart_availability_mode', true) ?: 'stock';

    if ($mode === 'backorder') {
        return ['label' => __('Na objednávku', 'graceart'), 'in_stock' => false];
    }

    if ($product->get_stock_status() === 'outofstock') {
        return ['label' => __('Nie je skladom', 'graceart'), 'in_stock' => false];
    }

    return ['label' => __('Skladom', 'graceart'), 'in_stock' => true];
}

function graceartShippingMethodCostLabel(WC_Shipping_Method $method): string
{
    if ($method->id === 'free_shipping') {
        return __('Zadarmo', 'graceart');
    }

    if (! method_exists($method, 'get_option')) {
        return '';
    }

    $cost = $method->get_option('cost');

    if ($cost === '' || ! is_numeric($cost)) {
        return '';
    }

    return (float) $cost > 0 ? wp_strip_all_tags(wc_price((float) $cost)) : __('Zadarmo', 'graceart');
}

function graceartShippingCountryLabels(): array
{
    return [
        'SK' => __('Slovenská republika', 'graceart'),
        'CZ' => __('Česká republika', 'graceart'),
    ];
}

function graceartShippingMethodsByCountry(): array
{
    if (! class_exists('WC_Shipping_Zones')) {
        return [];
    }

    $country_labels = graceartShippingCountryLabels();
    $groups = [];

    foreach (WC_Shipping_Zones::get_zones() as $zone_data) {
        $zone = new WC_Shipping_Zone($zone_data['zone_id']);
        $country_code = null;

        foreach ($zone->get_zone_locations() as $location) {
            if ($location->type === 'country' && isset($country_labels[$location->code])) {
                $country_code = $location->code;

                break;
            }
        }

        if (! $country_code || isset($groups[$country_code])) {
            continue;
        }

        $methods = [];

        foreach ($zone->get_shipping_methods() as $method) {
            if (! $method instanceof WC_Shipping_Method || ! $method->is_enabled()) {
                continue;
            }

            $methods[] = [
                'title' => $method->get_title() === 'Free shipping' ? __('Doprava zdarma', 'graceart') : $method->get_title(),
                'cost' => graceartShippingMethodCostLabel($method),
            ];
        }

        if ($methods) {
            $groups[$country_code] = [
                'label' => $country_labels[$country_code],
                'methods' => $methods,
            ];
        }
    }

    uksort($groups, fn(string $a, string $b): int => array_search($a, array_keys($country_labels), true) <=> array_search($b, array_keys($country_labels), true));

    return $groups;
}

function graceartCardPaymentEnabled(): bool
{
    if (! function_exists('WC')) {
        return false;
    }

    return array_key_exists('cheque', WC()->payment_gateways()->get_available_payment_gateways());
}

/**
 * Sold-out variants are not offered at all; backordered ones still are.
 */
function graceartVariationIsOffered(int $variation_id): bool
{
    $variation = wc_get_product($variation_id);

    if (! $variation instanceof WC_Product) {
        return false;
    }

    if (get_post_meta($variation_id, '_graceart_availability_mode', true) === 'backorder') {
        return true;
    }

    return $variation->is_in_stock();
}

function graceartOfferedVariations(array $variations): array
{
    $offered = array_values(array_filter(
        $variations,
        fn(array $variation): bool => graceartVariationIsOffered((int) $variation['variation_id'])
    ));

    return $offered ?: $variations;
}

function graceartResolveSelectedVariationData(WC_Product $product): ?array
{
    if (! $product->is_type('variable')) {
        return null;
    }

    $variations = $product->get_available_variations();

    if (! $variations) {
        return null;
    }

    $variations = graceartOfferedVariations($variations);

    foreach ($variations as $variation) {
        $matches = true;

        foreach ($variation['attributes'] as $attribute_key => $attribute_value) {
            if ($attribute_value === '') {
                continue;
            }

            $requested = isset($_GET[$attribute_key]) ? sanitize_title(wp_unslash($_GET[$attribute_key])) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

            if ($requested !== $attribute_value) {
                $matches = false;

                break;
            }
        }

        if ($matches) {
            return $variation;
        }
    }

    return $variations[0];
}

function graceartProductLoopPermalink(WC_Product $product): string
{
    $url = get_permalink($product->get_id());

    $variation_data = graceartResolveSelectedVariationData($product);

    if (! $variation_data) {
        return $url;
    }

    return add_query_arg($variation_data['attributes'], $url);
}

function graceartProductLoopPriceHtml(WC_Product $product): string
{
    $variation_data = graceartResolveSelectedVariationData($product);

    if (! $variation_data) {
        return $product->get_price_html();
    }

    $variation = wc_get_product($variation_data['variation_id']);

    return $variation instanceof WC_Product ? $variation->get_price_html() : $product->get_price_html();
}
