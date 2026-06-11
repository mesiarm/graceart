<?php

add_filter('wc_add_to_cart_message_html', function (string $message, array $products, bool $show_qty): string {
    $product_names = [];

    foreach ($products as $product_id => $quantity) {
        $product = wc_get_product($product_id);

        if (! $product instanceof WC_Product) {
            continue;
        }

        $name = $product->get_name();

        if ($show_qty && $quantity > 1) {
            $name = sprintf('%s &times; %d', $name, $quantity);
        }

        $product_names[] = '&bdquo;' . esc_html($name) . '&ldquo;';
    }

    if (! $product_names) {
        return $message;
    }

    $cart_link = sprintf(
        '<a href="%s" tabindex="1" class="button wc-forward btn btn-dark btn-hover-primary">%s</a>',
        esc_url(wc_get_cart_url()),
        esc_html__('Zobraziť košík', 'graceart'),
    );

    $notice_text = count($product_names) === 1
        ? sprintf(__('Produkt %s bol pridaný do košíka.', 'graceart'), $product_names[0])
        : sprintf(__('Produkty %s boli pridané do košíka.', 'graceart'), implode(', ', $product_names));

    return $cart_link . wp_kses_post($notice_text);
}, 10, 3);
