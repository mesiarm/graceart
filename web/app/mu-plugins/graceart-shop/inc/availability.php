<?php

/**
 * Availability "na objednávku": pieces that can be made to order on top of the
 * stock, and the lead time ("do 3 dní") shown for them.
 *
 * Admin: both fields sit with the stock fields of a product and of each
 * variation. Orders: every line remembers at purchase whether it was made to
 * order (hidden meta), and the order screen, the e-mails and the PDF invoice
 * print one "Dostupnosť" for the whole order from it.
 */

function graceartLeadTimeOptions(): array
{
    return [
        '3_dni' => __('3 dni', 'graceart'),
        '1_tyzden' => __('1 týždeň', 'graceart'),
        '2_tyzdne' => __('2 týždne', 'graceart'),
    ];
}

/**
 * Lead times in the genitive form used after "do" ("do 1 týždňa").
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
        return graceartLeadTimePhrase($product);
    }

    if (! $product->is_in_stock()) {
        return __('Nie je skladom', 'graceart');
    }

    // No count: the number in stock is the shop's business, not the shopper's.
    return __('Skladom', 'graceart');
}

/**
 * Availability of a number of pieces (a cart line, an order item) rather than
 * of the product: a line is shipped whole, so once the stock covers only part
 * of the quantity the whole line is made to order, and the line says so
 * instead of the product's "Skladom" under a line the stock cannot fill.
 */
function graceartQuantityAvailabilityText(WC_Product $product, int $quantity): string
{
    if (! graceartQuantityIsOnBackorder($product, $quantity)) {
        return graceartAvailabilityText($product);
    }

    return graceartLeadTimePhrase($product);
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

    return graceartLeadTimePhrases()[$lead_time];
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

/**
 * WooCommerce adds its own "Backordered: 2" item meta (Slovak "Objednané: 2")
 * to a line whose quantity exceeds the stock, and prints it to the customer
 * on the thank-you page, in the emails and on the invoice — where the
 * order-wide "Dostupnosť" already says the order is made to order. Under a
 * "_" key it is hidden from all of them; the admin order screen still gets
 * it, as a readable line under the item.
 */
add_filter('woocommerce_backordered_item_meta_name', fn(): string => '_graceart_backordered');

/**
 * The keys WooCommerce gave that meta on orders placed before the rename
 * ("Objednané" in Slovak, "Backordered" in English).
 */
function graceartLegacyBackorderedMetaKeys(): array
{
    return array_unique(['Backordered', __('Backordered', 'woocommerce')]);
}

// Orders placed before the rename lose the line the same way.
add_filter('woocommerce_order_item_get_formatted_meta_data', function (array $formatted_meta): array {
    foreach ($formatted_meta as $meta_id => $meta) {
        if (isset($meta->key) && in_array($meta->key, graceartLegacyBackorderedMetaKeys(), true)) {
            unset($formatted_meta[$meta_id]);
        }
    }

    return $formatted_meta;
});

add_filter('woocommerce_hidden_order_itemmeta', function (array $hidden): array {
    $hidden[] = '_graceart_backordered';
    $hidden[] = '_graceart_lead_time';

    return array_merge($hidden, graceartLegacyBackorderedMetaKeys());
});

add_action('woocommerce_after_order_itemmeta', function (int $item_id, WC_Order_Item $item): void {
    if (! $item instanceof WC_Order_Item_Product) {
        return;
    }

    $backordered = (int) $item->get_meta('_graceart_backordered');

    foreach (graceartLegacyBackorderedMetaKeys() as $legacy_key) {
        $backordered = $backordered > 0 ? $backordered : (int) $item->get_meta($legacy_key);
    }

    if ($backordered <= 0) {
        return;
    }

    printf(
        '<div class="wc-order-item-graceart-backordered"><strong>%s:</strong> %s</div>',
        esc_html__('Na objednávku', 'graceart'),
        esc_html(sprintf(__('%d ks', 'graceart'), $backordered)),
    );
}, 10, 2);

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
