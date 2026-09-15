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
        return __('Vybrať variant', 'graceart');
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

/**
 * A plain link, not the [yith_wcwl_add_to_wishlist] shortcode: that shortcode
 * only prints an empty placeholder for YITH's React bundle to fill in, and the
 * bundle is dequeued everywhere but the wishlist page (see inc/assets.php).
 * YITH's form handler adds the product from ?add_to_wishlist=ID&_wpnonce=….
 */
/**
 * Whether the product is already in the visitor's (default) wishlist.
 */
function graceartIsInWishlist(WC_Product $product): bool
{
    return function_exists('yith_wcwl_wishlists')
        && (bool) yith_wcwl_wishlists()->is_product_in_wishlist($product->get_id());
}

/**
 * A heart that toggles: outline + add link, or filled + remove link when the
 * product is already in the wishlist. Both are plain URLs YITH's form handler
 * understands (its React bundle is not loaded here).
 */
function graceartWishlistButton(WC_Product $product): string
{
    if (graceartIsInWishlist($product)) {
        return sprintf(
            '<a href="%1$s" class="graceart-wishlist-button is-active hintT-top" data-hint="%2$s" aria-label="%2$s" aria-pressed="true"><i class="fas fa-heart" aria-hidden="true"></i></a>',
            esc_url(wp_nonce_url(add_query_arg('remove_from_wishlist', $product->get_id(), $product->get_permalink()), 'remove_from_wishlist')),
            esc_attr__('Odstrániť zo zoznamu prianí', 'graceart'),
        );
    }

    return sprintf(
        '<a href="%1$s" class="graceart-wishlist-button hintT-top" data-hint="%2$s" aria-label="%2$s" aria-pressed="false"><i class="far fa-heart" aria-hidden="true"></i></a>',
        esc_url(graceartWishlistProductUrl($product)),
        esc_attr__('Pridať do zoznamu prianí', 'graceart'),
    );
}

/**
 * YITH removes the item on "init"; drop the query string so a refresh does
 * not repeat the request. No notice: the heart icon going hollow is enough.
 */
add_action('template_redirect', function (): void {
    if (! isset($_GET['remove_from_wishlist'], $_GET['_wpnonce'])) {
        return;
    }

    if (! wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'remove_from_wishlist')) {
        return;
    }

    wp_safe_redirect(remove_query_arg(['remove_from_wishlist', '_wpnonce']));
    exit;
});

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
            'thumb_size' => '',
            'full' => wc_placeholder_img_src('woocommerce_single'),
            'large' => wc_placeholder_img_src('woocommerce_single'),
            'large_size' => '',
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
            'thumb_size' => graceartImageSizeAttr($image_id, 'woocommerce_thumbnail'),
            'full' => $full[0] ?? wp_get_attachment_image_url($image_id, 'full'),
            'large' => wp_get_attachment_image_url($image_id, 'woocommerce_single'),
            'large_size' => graceartImageSizeAttr($image_id, 'woocommerce_single'),
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

    $filters = array_map(function (WP_Term $term): array {
        $link = get_term_link($term);

        return [
            'label' => $term->slug === 'uncategorized' ? __('Nezaradené', 'graceart') : $term->name,
            'filter' => '.cat-' . $term->term_id,
            'term_id' => $term->term_id,
            'url' => is_wp_error($link) ? '' : $link,
        ];
    }, $terms);

    return array_values(array_filter($filters, function (array $filter): bool {
        return $filter['url'] !== '';
    }));
}

function graceartProductBadgeHtml(WC_Product $product): string
{
    $badges = [];

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

/**
 * ' width="…" height="…"' for an attachment at a size, so the browser can
 * reserve the box before the file arrives (no layout shift). Empty when the
 * size is unknown.
 */
function graceartImageSizeAttr(int $image_id, string $size): string
{
    $src = $image_id ? wp_get_attachment_image_src($image_id, $size) : false;

    if (! $src || empty($src[1]) || empty($src[2])) {
        return '';
    }

    return sprintf(' width="%d" height="%d"', (int) $src[1], (int) $src[2]);
}

function graceartProductImageSizeAttr(WC_Product $product, string $size = 'woocommerce_thumbnail'): string
{
    return graceartImageSizeAttr((int) $product->get_image_id(), $size);
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

add_action('woocommerce_product_options_stock_fields', function (): void {
    woocommerce_wp_text_input([
        'id' => '_graceart_backorder_qty',
        'label' => __('Počet na objednávku', 'graceart'),
        'description' => __('Počet kusov dostupných na objednávku po vypredaní skladových kusov.', 'graceart'),
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
    <script>
    jQuery(function ($) {
        // Stock is always tracked: the "Manage stock?" toggle is forced on and
        // hidden, and the quantity cannot be left blank (0 is fine — sold out).
        // WooCommerce's "Množstvo" is named like the theme's own fields.
        function requireStock($checkbox, $quantity) {
            $checkbox.prop('checked', true).trigger('change').closest('.form-field, label').hide();
            $quantity.attr({ required: 'required', min: '0' });
            $quantity.closest('.form-field, .form-row').find('label').first().text(<?php echo wp_json_encode(__('Počet na sklade', 'graceart')); ?>);
        }

        if ($('#product-type').val() !== 'variable') {
            $('.form-field._backorders_field, .form-field._low_stock_amount_field').remove();
            requireStock($('#_manage_stock'), $('#_stock'));
        }

        // The Inventory panel may be collapsed when Publish/Update runs, and a
        // hidden required field cannot show the browser's validation message.
        $('#post').on('submit', function () {
            var stock = $('#_stock')[0];

            if (stock && $('#product-type').val() !== 'variable' && ! stock.checkValidity()) {
                $('.inventory_options a, .inventory_tab a').first().trigger('click');
            }
        });

        // Same cleanup, per variation, whenever variation rows are (re)loaded.
        $('#woocommerce-product-data, #variable_product_options').on('woocommerce_variations_loaded woocommerce_variations_added', function () {
            $('.woocommerce_variation').each(function () {
                var $variation = $(this);

                if ($variation.data('graceart-processed')) {
                    return;
                }

                $variation.data('graceart-processed', true);

                // Only the fields' own <p>s: the div wrapping the whole stock
                // block is a .form-row too and holds the quantity.
                $variation.find('p.form-row:has(select[name^="variable_backorders["]), p.form-row:has(input[name^="variable_low_stock_amount["])').remove();
                requireStock($variation.find('input[name^="variable_manage_stock["]'), $variation.find('input[name^="variable_stock["]'));

                // The stock quantity sits with the theme's availability fields
                // instead of between the price and the shipping class.
                $variation.find('.show_if_variation_manage_stock').insertAfter($variation.find('.graceart-variation-availability'));
            });
        });

        $('#woocommerce-product-data').trigger('woocommerce_variations_loaded');
    });
    </script>
    <?php
});

// Saved before WooCommerce saves the product, so the stock status it works
// out from the derived backorders flag sees the fresh availability fields.
add_action('woocommerce_admin_process_product_object', function (WC_Product $product): void {
    $post_id = $product->get_id();

    if ($product->is_type('simple')) {
        graceartRequireStockQuantity($product, $_POST['_stock'] ?? null);
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
    $backorder_qty = get_post_meta($variation->ID, '_graceart_backorder_qty', true);
    $lead_time = get_post_meta($variation->ID, '_graceart_lead_time', true) ?: '3_dni';
    ?>
    <p class="form-row form-row-full graceart-variation-availability">
        <strong><?php esc_html_e('Dostupnosť', 'graceart'); ?></strong>
    </p>
    <?php
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
        'label' => __('Dodacia lehota (na objednávku)', 'graceart'),
        'value' => $lead_time,
        'options' => graceartLeadTimeOptions(),
        'wrapper_class' => 'form-row form-row-last',
    ]);
}, 10, 3);

// Same for variations: before each variation's save.
add_action('woocommerce_admin_process_variation_object', function (WC_Product_Variation $variation, int $loop): void {
    $variation_id = $variation->get_id();

    graceartRequireStockQuantity($variation, $_POST['variable_stock'][$loop] ?? null);

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

/**
 * Every sellable product tracks its stock, so the availability row can always
 * show a count. The admin screen forces the toggle on; this backs it up on
 * save, and a blanked quantity is reported (WooCommerce stores it as 0, so
 * nothing gets oversold meanwhile).
 */
function graceartRequireStockQuantity(WC_Product $product, $posted_quantity): void
{
    $product->set_manage_stock(true);

    if ($posted_quantity !== null && trim((string) wp_unslash($posted_quantity)) !== '') {
        return;
    }

    WC_Admin_Meta_Boxes::add_error(sprintf(
        /* translators: %s: product or variation name */
        __('Skladové množstvo je povinné (%s). Uložilo sa 0 ks.', 'graceart'),
        $product->get_name(),
    ));
}

// WooCommerce's own "X na sklade" line duplicates the theme's "Dostupnosť" row,
// on simple products and in the availability_html of variations alike.
add_filter('woocommerce_get_stock_html', function (string $html): string {
    return is_admin() ? $html : '';
});

/**
 * Pieces that can be made to order, on top of the stock.
 */
function graceartBackorderQuantity(WC_Product $product): int
{
    return max(0, (int) get_post_meta($product->get_id(), '_graceart_backorder_qty', true));
}

/**
 * Everything that can still be sold: stock plus the made-to-order pieces.
 * Null when stock is not managed (unlimited). Stock goes negative as the
 * made-to-order pieces are sold, so the sum shrinks with every order.
 */
function graceartAvailableQuantity(WC_Product $product): ?int
{
    if (! $product->managing_stock()) {
        return null;
    }

    $stock = $product->get_stock_quantity();

    if ($stock === null) {
        return null;
    }

    return (int) $stock + graceartBackorderQuantity($product);
}

/**
 * Shown as "Na objednávku": tracked stock is sold out and there are still
 * pieces to be made to order.
 */
function graceartIsOnBackorder(WC_Product $product): bool
{
    $available = graceartAvailableQuantity($product);

    if ($available === null) {
        return false;
    }

    return graceartBackorderQuantity($product) > 0 && $available > 0 && (int) $product->get_stock_quantity() <= 0;
}

/**
 * WooCommerce's own backorders flag is hidden from the product screen; the
 * theme derives it from the availability fields instead. "notify" while
 * made-to-order pieces remain, so the product stays purchasable at zero
 * stock (status "onbackorder"), "no" once those are gone too — WooCommerce
 * then marks the product out of stock on the next stock change or save, and
 * the listings drop it.
 */
function graceartDeriveBackorders(string $backorders, WC_Product $product): string
{
    if (! $product->managing_stock()) {
        return $backorders;
    }

    $available = graceartAvailableQuantity($product);

    if ($available === null || graceartBackorderQuantity($product) <= 0) {
        return 'no';
    }

    return $available > 0 ? 'notify' : 'no';
}

add_filter('woocommerce_product_get_backorders', 'graceartDeriveBackorders', 10, 2);
add_filter('woocommerce_product_variation_get_backorders', 'graceartDeriveBackorders', 10, 2);

add_filter('woocommerce_product_is_in_stock', function (bool $in_stock, WC_Product $product): bool {
    $available = graceartAvailableQuantity($product);

    return $available === null ? $in_stock : $available > 0;
}, 10, 2);

/**
 * Made-to-order pieces are capped too: the quantity picker (Store API cart
 * and classic alike goes through this) stops at stock + made-to-order.
 */
add_filter('woocommerce_quantity_input_max', function ($max, WC_Product $product) {
    $available = graceartAvailableQuantity($product);

    if ($available === null || graceartBackorderQuantity($product) <= 0) {
        return $max;
    }

    return $max > 0 ? min((int) $max, $available) : $available;
}, 10, 2);

add_filter('woocommerce_store_api_product_quantity_maximum', function ($max, WC_Product $product, ?array $cart_item = null) {
    $available = graceartAvailableQuantity($product);

    if ($available === null) {
        return $max;
    }

    $cart_item_key = $cart_item !== null ? graceartCartItemKey($cart_item) : null;

    $available_for_line = max(0, $available - graceartQuantityInCartForProduct($product, $cart_item_key));

    return $max > 0 ? min((int) $max, $available_for_line) : $available_for_line;
}, 10, 3);

/**
 * Same cap on the way into the cart — WooCommerce lets any quantity through
 * once backorders are on.
 */
add_filter('woocommerce_add_to_cart_validation', function ($passed, $product_id, $quantity, $variation_id = 0) {
    if (! $passed) {
        return false;
    }

    $product = wc_get_product((int) $variation_id ?: (int) $product_id);

    if (! $product instanceof WC_Product || graceartCanAddToCart($product, max(1, (int) $quantity))) {
        return true;
    }

    wc_add_notice(__('Toľko kusov už nie je k dispozícii.', 'graceart'), 'error');

    return false;
}, 10, 4);

add_filter('woocommerce_update_cart_validation', function ($passed, string $cart_item_key, array $cart_item, int $quantity): bool {
    if (! $passed) {
        return false;
    }

    $product = $cart_item['data'] ?? null;

    if (! $product instanceof WC_Product || graceartCanSetCartQuantity($product, max(0, $quantity), $cart_item_key)) {
        return true;
    }

    wc_add_notice(__('Toľko kusov už nie je k dispozícii.', 'graceart'), 'error');

    return false;
}, 10, 4);

add_action('woocommerce_store_api_validate_cart_item', function (WC_Product $product, array $cart_item): void {
    $available = graceartAvailableQuantity($product);

    if ($available === null || graceartQuantityInCartForProduct($product) <= $available) {
        return;
    }

    throw new Exception(__('Toľko kusov už nie je k dispozícii.', 'graceart'));
}, 10, 2);

/**
 * The product's lead time as "do 2 týždňov" (the first option when none is set).
 */
function graceartLeadTimePhrase(WC_Product $product): string
{
    return graceartLeadTimePhrases()[graceartLeadTimeKey($product)];
}

function graceartAvailabilityText(WC_Product $product): string
{
    if (graceartIsOnBackorder($product)) {
        return sprintf(__('Na objednávku %s', 'graceart'), graceartLeadTimePhrase($product));
    }

    if (! $product->is_in_stock()) {
        return __('Nie je skladom', 'graceart');
    }

    $quantity = $product->get_stock_quantity();

    if ($product->managing_stock() && $quantity !== null) {
        return sprintf(__('Skladom %d ks', 'graceart'), $quantity);
    }

    return __('Skladom', 'graceart');
}

/**
 * Availability of a number of pieces (a cart line, an order item) rather than
 * of the product: a line is shipped whole, so once the stock covers only part
 * of the quantity the whole line is made to order, and the line says so
 * instead of the product's "Skladom 2 ks" under a line of 5.
 */
function graceartQuantityAvailabilityText(WC_Product $product, int $quantity): string
{
    if (! graceartQuantityIsOnBackorder($product, $quantity)) {
        return graceartAvailabilityText($product);
    }

    return sprintf(__('Na objednávku %s', 'graceart'), graceartLeadTimePhrase($product));
}

/**
 * Whether this many pieces are (partly) made to order: the product is on
 * backorder, or its stock does not cover the quantity.
 */
function graceartQuantityIsOnBackorder(WC_Product $product, int $quantity): bool
{
    if (graceartIsOnBackorder($product)) {
        return true;
    }

    $stock = $product->managing_stock() ? $product->get_stock_quantity() : null;

    return $stock !== null && graceartBackorderQuantity($product) > 0 && $quantity > (int) $stock;
}

/**
 * The lead time key of a product ("2_tyzdne"), the first option when unset.
 */
function graceartLeadTimeKey(WC_Product $product): string
{
    $lead_time = (string) get_post_meta($product->get_id(), '_graceart_lead_time', true);

    return array_key_exists($lead_time, graceartLeadTimePhrases()) ? $lead_time : (string) array_key_first(graceartLeadTimePhrases());
}

/**
 * An order ships as a whole, so its availability is the longest lead time
 * among its lines, or "Skladom" when every line ships from stock. Read from
 * the hidden lead-time meta the lines were given at checkout; null for
 * orders placed before that meta existed.
 */
function graceartOrderLeadTime(WC_Order $order): ?string
{
    $ranks = array_flip(array_keys(graceartLeadTimePhrases()));
    $longest = null;
    $known = false;

    foreach ($order->get_items() as $item) {
        if (! $item instanceof WC_Order_Item_Product || ! $item->meta_exists('_graceart_lead_time')) {
            continue;
        }

        $known = true;
        $lead_time = (string) $item->get_meta('_graceart_lead_time');

        if (isset($ranks[$lead_time]) && ($longest === null || $ranks[$lead_time] > $ranks[$longest])) {
            $longest = $lead_time;
        }
    }

    if (! $known) {
        return null;
    }

    return $longest ?? 'skladom';
}

function graceartOrderAvailabilityText(WC_Order $order): string
{
    $lead_time = graceartOrderLeadTime($order);

    if ($lead_time === null) {
        return '';
    }

    if ($lead_time === 'skladom') {
        return __('Skladom', 'graceart');
    }

    return sprintf(__('Na objednávku %s', 'graceart'), graceartLeadTimePhrases()[$lead_time]);
}

function graceartAvailabilityShortLabel(WC_Product $product): array
{
    if (graceartIsOnBackorder($product)) {
        return ['label' => __('Na objednávku', 'graceart'), 'in_stock' => false];
    }

    if (! $product->is_in_stock()) {
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

            // Free shipping has no fixed price; it is shown as the threshold
            // notice under the table instead of as a row with an empty cost.
            if ($method->id === 'free_shipping') {
                continue;
            }

            $methods[] = [
                'title' => $method->get_title(),
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

/**
 * Lowest order total that qualifies for free shipping, taken from the enabled
 * "Free shipping" methods in the WooCommerce shipping zones, or null when none
 * of them is set up with a minimum amount.
 */
function graceartFreeShippingMinAmount(): ?float
{
    if (! class_exists('WC_Shipping_Zones')) {
        return null;
    }

    $amounts = [];

    foreach (WC_Shipping_Zones::get_zones() as $zone_data) {
        $zone = new WC_Shipping_Zone($zone_data['zone_id']);

        foreach ($zone->get_shipping_methods() as $method) {
            if (! $method instanceof WC_Shipping_Method || $method->id !== 'free_shipping' || ! $method->is_enabled()) {
                continue;
            }

            if (! in_array($method->get_option('requires'), ['min_amount', 'either', 'both'], true)) {
                continue;
            }

            $amount = (float) $method->get_option('min_amount');

            if ($amount > 0) {
                $amounts[] = $amount;
            }
        }
    }

    return $amounts ? min($amounts) : null;
}

/**
 * Quantity of a given product (or variation) already sitting in the cart.
 * A null variation id counts every line of the product, whichever variation —
 * that is the number that matters when stock is managed on the parent.
 */
function graceartQuantityInCart(int $product_id, ?int $variation_id = 0, ?string $exclude_cart_item_key = null): int
{
    if (! function_exists('WC') || ! WC()->cart) {
        return 0;
    }

    $total = 0;

    foreach (WC()->cart->get_cart() as $cart_item_key => $item) {
        if ($exclude_cart_item_key !== null && $cart_item_key === $exclude_cart_item_key) {
            continue;
        }

        $item_variation = (int) ($item['variation_id'] ?? 0);

        if ($variation_id > 0) {
            if ($item_variation === $variation_id) {
                $total += (int) $item['quantity'];
            }
            continue;
        }

        if ((int) ($item['product_id'] ?? 0) !== $product_id) {
            continue;
        }

        if ($variation_id === null || $item_variation === 0) {
            $total += (int) $item['quantity'];
        }
    }

    return $total;
}

function graceartCartItemKey(array $cart_item): ?string
{
    if (! function_exists('WC') || ! WC()->cart) {
        return null;
    }

    foreach (WC()->cart->get_cart() as $cart_item_key => $item) {
        if ($item === $cart_item) {
            return (string) $cart_item_key;
        }
    }

    return null;
}

function graceartQuantityInCartForProduct(WC_Product $product, ?string $exclude_cart_item_key = null): int
{
    if (! $product->is_type('variation')) {
        return graceartQuantityInCart($product->get_id(), 0, $exclude_cart_item_key);
    }

    if ($product->managing_stock() === 'parent') {
        return graceartQuantityInCart((int) $product->get_parent_id(), null, $exclude_cart_item_key);
    }

    return graceartQuantityInCart((int) $product->get_parent_id(), $product->get_id(), $exclude_cart_item_key);
}

function graceartCanSetCartQuantity(WC_Product $product, int $qty, ?string $cart_item_key = null): bool
{
    if ($qty <= 0) {
        return true;
    }

    if (! $product->is_purchasable() || ! $product->is_in_stock()) {
        return false;
    }

    $available = graceartAvailableQuantity($product);

    if ($available === null) {
        return true;
    }

    $other_in_cart = graceartQuantityInCartForProduct($product, $cart_item_key);

    return ($available - $other_in_cart) >= $qty;
}

/**
 * Whether another $qty of this product can still be added, taking into account
 * what the cart already holds. Without this the listing offers "Kúpiť" for a
 * one-off piece that is already in the cart, and WooCommerce rejects the add.
 *
 * @param WC_Product $product Simple product, or the variation itself.
 */
function graceartCanAddToCart(WC_Product $product, int $qty = 1): bool
{
    if (! $product->is_purchasable() || ! $product->is_in_stock()) {
        return false;
    }

    $available = graceartAvailableQuantity($product);

    if ($available === null) {
        return true;
    }

    $in_cart = graceartQuantityInCartForProduct($product);

    return ($available - $in_cart) >= $qty;
}

/**
 * Whether the shopper can still add anything of this product: the product
 * itself, or for a variable product at least one of its variations.
 */
function graceartHasAddableStock(WC_Product $product): bool
{
    if (! $product->is_type('variable')) {
        return graceartCanAddToCart($product);
    }

    foreach ($product->get_children() as $variation_id) {
        $variation = wc_get_product($variation_id);

        if ($variation instanceof WC_Product && graceartCanAddToCart($variation)) {
            return true;
        }
    }

    return false;
}

/**
 * Products the shopper's own cart has emptied: a one-off piece (or every
 * variation of one) whose whole stock is already in the cart. For that shopper
 * they are sold out, so listings drop them like any other sold-out product.
 * Only products in the cart can qualify, so the check is cheap.
 */
function graceartCartExhaustedProductIds(): array
{
    if (is_admin() || ! function_exists('WC') || ! WC()->cart) {
        return [];
    }

    $items = WC()->cart->get_cart();

    if (! $items) {
        return [];
    }

    static $cache = [];

    $cache_key = md5(wp_json_encode(array_map(function (array $item): array {
        return [(int) ($item['product_id'] ?? 0), (int) ($item['variation_id'] ?? 0), (int) ($item['quantity'] ?? 0)];
    }, array_values($items))));

    if (isset($cache[$cache_key])) {
        return $cache[$cache_key];
    }

    $exhausted = [];

    foreach ($items as $item) {
        $product_id = (int) ($item['product_id'] ?? 0);

        if ($product_id <= 0 || isset($exhausted[$product_id])) {
            continue;
        }

        $product = wc_get_product($product_id);

        if ($product instanceof WC_Product && ! graceartHasAddableStock($product)) {
            $exhausted[$product_id] = true;
        }
    }

    return $cache[$cache_key] = array_keys($exhausted);
}

/**
 * Sold-out products are not listed anywhere — shop, categories, search,
 * related products, homepage bestsellers. WooCommerce hides them for the
 * catalog queries and is_visible() once this option is on; it is forced here
 * rather than left to the settings screen.
 */
add_filter('pre_option_woocommerce_hide_out_of_stock_items', fn(): string => 'yes');

add_filter('woocommerce_product_is_visible', function (bool $visible, int $product_id): bool {
    if (! $visible || in_array($product_id, graceartCartExhaustedProductIds(), true)) {
        return false;
    }

    $product = wc_get_product($product_id);

    return $product instanceof WC_Product && graceartHasAddableStock($product);
}, 10, 2);

/**
 * Drop cart-exhausted products from the listing query itself, so pages stay
 * full rather than rendering a gap where content-product.php bails out.
 */
function graceartExcludeCartExhaustedFromQuery(WP_Query $query): void
{
    $exhausted = graceartCartExhaustedProductIds();

    if (! $exhausted) {
        return;
    }

    $not_in = $query->get('post__not_in');
    $not_in = is_array($not_in) ? $not_in : [];
    $query->set('post__not_in', array_values(array_unique(array_merge($not_in, $exhausted))));
}

add_action('woocommerce_product_query', 'graceartExcludeCartExhaustedFromQuery');

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

    return graceartCanAddToCart($variation);
}

function graceartOfferedVariations(array $variations): array
{
    $offered = array_values(array_filter(
        $variations,
        fn(array $variation): bool => graceartVariationIsOffered((int) $variation['variation_id']),
    ));

    return $offered;
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

/**
 * The product a listing card (or wishlist row) describes: the product itself,
 * or for a variable product the variation the card links to, so the price
 * and the availability come from the same piece.
 */
function graceartProductLoopDisplayProduct(WC_Product $product): WC_Product
{
    $variation_data = graceartResolveSelectedVariationData($product);

    if (! $variation_data) {
        return $product;
    }

    $variation = wc_get_product($variation_data['variation_id']);

    return $variation instanceof WC_Product ? $variation : $product;
}

function graceartProductLoopPriceHtml(WC_Product $product): string
{
    return graceartProductLoopDisplayProduct($product)->get_price_html();
}

/**
 * The "Dostupnosť" line of the product page under every cart line as well:
 * the cart and checkout blocks print item data under the product name, and
 * so does the classic cart. The cart item's product is the variation when
 * one was chosen, so the count is the variant's; the line's quantity decides
 * whether part of it is made to order (the blocks' own "Na objednávku" badge
 * is hidden in CSS, this line says it with the numbers).
 */
add_filter('woocommerce_get_item_data', function (array $item_data, array $cart_item): array {
    $product = $cart_item['data'] ?? null;

    if (! $product instanceof WC_Product) {
        return $item_data;
    }

    $item_data[] = [
        'key' => __('Dostupnosť', 'graceart'),
        'value' => graceartQuantityAvailabilityText($product, (int) ($cart_item['quantity'] ?? 1)),
        'className' => 'graceart-cart-item-availability',
    ];

    return $item_data;
}, 10, 2);

/**
 * Each order line remembers whether it was made to order and with what lead
 * time ("2_tyzdne", or "skladom") as hidden meta set at order time — before
 * the stock is reduced, so it says what the site said at purchase. The order
 * emails, the thank-you page, the admin order screen and the PDF invoice
 * print one "Dostupnosť" for the whole order from it.
 */
add_action('woocommerce_checkout_create_order_line_item', function (WC_Order_Item_Product $item, string $cart_item_key, array $values): void {
    $product = $values['data'] ?? null;

    if (! $product instanceof WC_Product) {
        return;
    }

    $on_backorder = graceartQuantityIsOnBackorder($product, (int) ($values['quantity'] ?? 1));

    $item->add_meta_data('_graceart_lead_time', $on_backorder ? graceartLeadTimeKey($product) : 'skladom', true);
}, 10, 3);

add_filter('woocommerce_email_order_meta_fields', function (array $fields, bool $sent_to_admin, WC_Order $order): array {
    $availability = graceartOrderAvailabilityText($order);

    if ($availability !== '') {
        $fields['graceart_availability'] = [
            'label' => __('Dostupnosť', 'graceart'),
            'value' => $availability,
        ];
    }

    return $fields;
}, 10, 3);

add_action('woocommerce_admin_order_data_after_order_details', function (WC_Order $order): void {
    $availability = graceartOrderAvailabilityText($order);

    if ($availability === '') {
        return;
    }

    printf(
        '<p class="form-field form-field-wide graceart-order-availability"><strong>%s:</strong> %s</p>',
        esc_html__('Dostupnosť', 'graceart'),
        esc_html($availability),
    );
});
