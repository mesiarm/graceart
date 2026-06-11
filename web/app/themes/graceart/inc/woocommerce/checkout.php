<?php

add_filter('woocommerce_order_button_text', fn(): string => __('Objednať s povinnosťou platby', 'graceart'));

add_filter('woocommerce_checkout_must_be_logged_in_message', fn(): string => __('Pre dokončenie objednávky sa musíte prihlásiť.', 'graceart'));

add_filter('woocommerce_form_field_args', function (array $args): array {
    $args['class'][] = 'learts-mb-20';
    $args['input_class'][] = 'graceart-input';

    return $args;
});

add_filter('woocommerce_default_address_fields', function (array $fields): array {
    $labels = [
        'first_name' => __('Meno', 'graceart'),
        'last_name' => __('Priezvisko', 'graceart'),
        'company' => __('Spoločnosť', 'graceart'),
        'country' => __('Krajina / región', 'graceart'),
        'address_1' => __('Ulica a číslo domu', 'graceart'),
        'address_2' => __('Byt, apartmán, jednotka a pod.', 'graceart'),
        'city' => __('Mesto', 'graceart'),
        'state' => __('Kraj', 'graceart'),
        'postcode' => __('PSČ', 'graceart'),
    ];

    $placeholders = [
        'address_1' => __('Názov ulice a číslo domu', 'graceart'),
        'address_2' => __('Byt, apartmán, jednotka a pod. (voliteľné)', 'graceart'),
    ];

    foreach ($labels as $key => $label) {
        if (isset($fields[$key])) {
            $fields[$key]['label'] = $label;
        }
    }

    foreach ($placeholders as $key => $placeholder) {
        if (isset($fields[$key])) {
            $fields[$key]['placeholder'] = $placeholder;
        }
    }

    return $fields;
});

add_filter('woocommerce_checkout_fields', function (array $fields): array {
    if (isset($fields['billing']['billing_phone'])) {
        $fields['billing']['billing_phone']['label'] = __('Telefón', 'graceart');
    }

    if (isset($fields['billing']['billing_email'])) {
        $fields['billing']['billing_email']['label'] = __('E-mailová adresa', 'graceart');
    }

    if (isset($fields['order']['order_comments'])) {
        $fields['order']['order_comments']['label'] = __('Poznámka k objednávke', 'graceart');
        $fields['order']['order_comments']['placeholder'] = __('Poznámky k objednávke, napríklad špeciálne požiadavky na doručenie.', 'graceart');
    }

    return $fields;
});

add_filter('gettext_woocommerce', function (string $translation, string $text): string {
    $translations = [
        'Cart updated.' => __('Košík bol aktualizovaný.', 'graceart'),
        'Update cart' => __('Aktualizovať košík', 'graceart'),
        'Apply coupon' => __('Použiť kupón', 'graceart'),
        'Coupon code' => __('Kód kupónu', 'graceart'),
        'Coupon:' => __('Kupón', 'graceart'),
        'Remove item' => __('Odstrániť položku', 'graceart'),
        'Thumbnail image' => __('Náhľad produktu', 'graceart'),
        'Product' => __('Produkt', 'graceart'),
        'Price' => __('Cena', 'graceart'),
        'Quantity' => __('Množstvo', 'graceart'),
        'Subtotal' => __('Medzisúčet', 'graceart'),
        'Total' => __('Spolu', 'graceart'),
        'Shipping' => __('Doprava', 'graceart'),
        'Cart totals' => __('Súhrn košíka', 'graceart'),
        'Proceed to checkout' => __('Pokračovať k pokladni', 'graceart'),
        'Checkout' => __('Pokladňa', 'graceart'),
        'Billing details' => __('Fakturačné údaje', 'graceart'),
        'Your order' => __('Vaša objednávka', 'graceart'),
        'Place order' => __('Objednať s povinnosťou platby', 'graceart'),
        'Returning customer?' => __('Už ste u nás nakupovali?', 'graceart'),
        'Click here to login' => __('Kliknite sem a prihláste sa', 'graceart'),
        'Have a coupon?' => __('Máte kupón?', 'graceart'),
        'Click here to enter your code' => __('Kliknite sem a zadajte kód', 'graceart'),
        'Enter your coupon code' => __('Zadajte kód kupónu', 'graceart'),
        'Create an account?' => __('Vytvoriť účet?', 'graceart'),
        'Ship to a different address?' => __('Doručiť na inú adresu?', 'graceart'),
        'Order notes' => __('Poznámka k objednávke', 'graceart'),
        'Notes about your order, e.g. special notes for delivery.' => __('Poznámky k objednávke, napríklad špeciálne požiadavky na doručenie.', 'graceart'),
        'required' => __('povinné', 'graceart'),
        'optional' => __('voliteľné', 'graceart'),
        'Update totals' => __('Aktualizovať súhrn', 'graceart'),
        'Calculate shipping' => __('Vypočítať dopravu', 'graceart'),
        'Update' => __('Aktualizovať', 'graceart'),
        'Country / region' => __('Krajina / región', 'graceart'),
        'Select a country / region&hellip;' => __('Vyberte krajinu / región...', 'graceart'),
        'State / County' => __('Kraj', 'graceart'),
        'Select an option&hellip;' => __('Vyberte možnosť...', 'graceart'),
        'City:' => __('Mesto', 'graceart'),
        'Postcode / ZIP:' => __('PSČ', 'graceart'),
        'There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.' => __('Nie sú dostupné žiadne možnosti dopravy. Skontrolujte zadanú adresu alebo nás kontaktujte.', 'graceart'),
        'Enter your address to view shipping options.' => __('Zadajte adresu pre zobrazenie možností dopravy.', 'graceart'),
        'Shipping costs are calculated during checkout.' => __('Cena dopravy sa vypočíta v pokladni.', 'graceart'),
        'Shipping options will be updated during checkout.' => __('Možnosti dopravy sa aktualizujú počas pokladne.', 'graceart'),
        'Change address' => __('Zmeniť adresu', 'graceart'),
        'Enter a different address' => __('Zadať inú adresu', 'graceart'),
        'Available on backorder' => __('Dostupné na objednávku', 'graceart'),
        'is a required field.' => __('je povinné pole.', 'graceart'),
    ];

    return $translations[$text] ?? $translation;
}, 10, 2);
