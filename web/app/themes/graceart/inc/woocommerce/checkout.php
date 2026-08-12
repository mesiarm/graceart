<?php

add_filter('woocommerce_order_button_text', fn(): string => __('Objednať s povinnosťou platby', 'graceart'));

add_filter('woocommerce_checkout_must_be_logged_in_message', fn(): string => __('Pre dokončenie objednávky sa musíte prihlásiť.', 'graceart'));

add_filter('woocommerce_package_rates', function (array $rates): array {
    foreach ($rates as $rate) {
        if (is_object($rate) && method_exists($rate, 'get_label') && method_exists($rate, 'set_label') && $rate->get_label() === 'Free shipping') {
            $rate->set_label(__('Doprava zdarma', 'graceart'));
        }
    }

    return $rates;
});

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
        $fields['billing']['billing_phone']['required'] = true;
    }

    if (isset($fields['billing']['billing_email'])) {
        $fields['billing']['billing_email']['label'] = __('E-mailová adresa', 'graceart');
    }

    if (isset($fields['order']['order_comments'])) {
        $fields['order']['order_comments']['label'] = __('Poznámka k objednávke', 'graceart');
        $fields['order']['order_comments']['placeholder'] = __('Poznámky k objednávke, napríklad špeciálne požiadavky na doručenie.', 'graceart');
    }

    if (isset($fields['account']['account_username'])) {
        $fields['account']['account_username']['label'] = __('Používateľské meno', 'graceart');
    }

    if (isset($fields['account']['account_password'])) {
        $fields['account']['account_password']['label'] = __('Heslo', 'graceart');
    }

    return $fields;
});

add_filter('gettext_woocommerce', function (string $translation, string $text): string {
    $translations = [
        'Cart updated.' => __('Košík bol aktualizovaný.', 'graceart'),
        'Cart' => __('Košík', 'graceart'),
        'View cart' => __('Zobraziť košík', 'graceart'),
        'Your cart is currently empty.' => __('Váš košík je momentálne prázdny.', 'graceart'),
        'Return to shop' => __('Späť do obchodu', 'graceart'),
        'Continue shopping' => __('Pokračovať v nákupe', 'graceart'),
        'Update cart' => __('Aktualizovať košík', 'graceart'),
        'Apply coupon' => __('Použiť kupón', 'graceart'),
        'Coupon code' => __('Kód kupónu', 'graceart'),
        'Coupon:' => __('Kupón', 'graceart'),
        'Coupon code applied successfully.' => __('Kód kupónu bol úspešne použitý.', 'graceart'),
        'Coupon has been removed.' => __('Kupón bol odstránený.', 'graceart'),
        'Remove coupon' => __('Odstrániť kupón', 'graceart'),
        'Remove item' => __('Odstrániť položku', 'graceart'),
        'Remove this item' => __('Odstrániť túto položku', 'graceart'),
        'Undo?' => __('Vrátiť späť?', 'graceart'),
        'Thumbnail image' => __('Náhľad produktu', 'graceart'),
        'Product' => __('Produkt', 'graceart'),
        'Price' => __('Cena', 'graceart'),
        'Quantity' => __('Množstvo', 'graceart'),
        'Subtotal' => __('Medzisúčet', 'graceart'),
        'Total' => __('Spolu', 'graceart'),
        'Shipping' => __('Doprava', 'graceart'),
        'Cart totals' => __('Súhrn košíka', 'graceart'),
        'Proceed to checkout' => __('Pokračovať k pokladni', 'graceart'),
        'Proceed to Checkout' => __('Pokračovať k pokladni', 'graceart'),
        'Checkout' => __('Pokladňa', 'graceart'),
        'Billing &amp; Shipping' => __('Fakturácia a doprava', 'graceart'),
        'Billing & Shipping' => __('Fakturácia a doprava', 'graceart'),
        'Billing details' => __('Fakturačné údaje', 'graceart'),
        'Billing address' => __('Fakturačná adresa', 'graceart'),
        'Shipping address' => __('Dodacia adresa', 'graceart'),
        'Additional information' => __('Doplňujúce údaje', 'graceart'),
        'Your order' => __('Vaša objednávka', 'graceart'),
        'Place order' => __('Objednať s povinnosťou platby', 'graceart'),
        'Pay for order' => __('Zaplatiť objednávku', 'graceart'),
        'Returning customer?' => __('Už ste u nás nakupovali?', 'graceart'),
        'Click here to login' => __('Kliknite sem a prihláste sa', 'graceart'),
        'If you have shopped with us before, please enter your details below. If you are a new customer, please proceed to the Billing section.' => __('Ak ste u nás už nakupovali, zadajte svoje údaje nižšie. Ak ste nový zákazník, pokračujte prosím k fakturačným údajom.', 'graceart'),
        'Username or email address' => __('Používateľské meno alebo e-mailová adresa', 'graceart'),
        'Password' => __('Heslo', 'graceart'),
        'Remember me' => __('Zapamätať si ma', 'graceart'),
        'Log in' => __('Prihlásiť sa', 'graceart'),
        'Login' => __('Prihlásenie', 'graceart'),
        'Lost your password?' => __('Zabudli ste heslo?', 'graceart'),
        'Have a coupon?' => __('Máte kupón?', 'graceart'),
        'Click here to enter your code' => __('Kliknite sem a zadajte kód', 'graceart'),
        'Enter your coupon code' => __('Zadajte kód kupónu', 'graceart'),
        'Create an account?' => __('Vytvoriť účet?', 'graceart'),
        'Ship to a different address?' => __('Doručiť na inú adresu?', 'graceart'),
        'Order notes' => __('Poznámka k objednávke', 'graceart'),
        'Notes about your order, e.g. special notes for delivery.' => __('Poznámky k objednávke, napríklad špeciálne požiadavky na doručenie.', 'graceart'),
        'First name' => __('Meno', 'graceart'),
        'Last name' => __('Priezvisko', 'graceart'),
        'Company name' => __('Názov spoločnosti', 'graceart'),
        'Country / Region' => __('Krajina / región', 'graceart'),
        'Street address' => __('Ulica a číslo domu', 'graceart'),
        'Apartment, suite, unit, etc.' => __('Byt, apartmán, jednotka a pod.', 'graceart'),
        'Town / City' => __('Mesto', 'graceart'),
        'State / County' => __('Kraj', 'graceart'),
        'Postcode / ZIP' => __('PSČ', 'graceart'),
        'Phone' => __('Telefón', 'graceart'),
        'Email address' => __('E-mailová adresa', 'graceart'),
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
        'No products in the cart.' => __('V košíku nie sú žiadne produkty.', 'graceart'),
        'No available payment methods' => __('Nie sú dostupné žiadne platobné metódy', 'graceart'),
        'Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.' => __('Ľutujeme, momentálne nie sú dostupné žiadne platobné metódy. Kontaktujte nás, prosím.', 'graceart'),
        'Please fill in your details above to see available payment methods.' => __('Vyplňte údaje vyššie, aby sa zobrazili dostupné platobné metódy.', 'graceart'),
        'Since your browser does not support JavaScript, or it is disabled, please ensure you click the %1$sUpdate totals%2$s button before placing your order. You may be charged more than the amount stated above if you fail to do so.' => __('Keďže váš prehliadač nepodporuje JavaScript alebo je vypnutý, pred odoslaním objednávky kliknite na tlačidlo %1$sAktualizovať súhrn%2$s. Inak sa môže účtovať iná suma, než je uvedená vyššie.', 'graceart'),
        'is a required field.' => __('je povinné pole.', 'graceart'),
    ];

    return $translations[$text] ?? $translation;
}, 10, 2);

add_action('wp_enqueue_scripts', function (): void {
    if (
        (! function_exists('is_cart') || ! is_cart())
        && (! function_exists('is_checkout') || ! is_checkout())
    ) {
        return;
    }

    $translations = [
        'Add a coupon' => __('Pridať kupón', 'graceart'),
        'Add coupons' => __('Pridať kupóny', 'graceart'),
        'Add apartment, suite, etc.' => __('Pridať byt, apartmán, jednotku a pod.', 'graceart'),
        'Address' => __('Adresa', 'graceart'),
        'Address (optional)' => __('Adresa (voliteľné)', 'graceart'),
        'Apartment, suite, etc.' => __('Byt, apartmán, jednotka a pod.', 'graceart'),
        'Apartment, suite, etc. (optional)' => __('Byt, apartmán, jednotka a pod. (voliteľné)', 'graceart'),
        'Apply' => __('Použiť', 'graceart'),
        'Cart' => __('Košík', 'graceart'),
        'Checkout' => __('Pokladňa', 'graceart'),
        'City' => __('Mesto', 'graceart'),
        'City (optional)' => __('Mesto (voliteľné)', 'graceart'),
        'Company' => __('Spoločnosť', 'graceart'),
        'Company (optional)' => __('Spoločnosť (voliteľné)', 'graceart'),
        'Contact information' => __('Kontaktné údaje', 'graceart'),
        'Country/Region' => __('Krajina / región', 'graceart'),
        'Country/Region (optional)' => __('Krajina / región (voliteľné)', 'graceart'),
        'Coupon code' => __('Kód kupónu', 'graceart'),
        'Edit' => __('Upraviť', 'graceart'),
        'Email address' => __('E-mailová adresa', 'graceart'),
        'Email address (optional)' => __('E-mailová adresa (voliteľné)', 'graceart'),
        'Enter code' => __('Zadajte kód', 'graceart'),
        'FREE' => __('ZDARMA', 'graceart'),
        'Free shipping' => __('Doprava zdarma', 'graceart'),
        'First name' => __('Meno', 'graceart'),
        'First name (optional)' => __('Meno (voliteľné)', 'graceart'),
        'Last name' => __('Priezvisko', 'graceart'),
        'Last name (optional)' => __('Priezvisko (voliteľné)', 'graceart'),
        'Order summary' => __('Súhrn objednávky', 'graceart'),
        'Payment options' => __('Možnosti platby', 'graceart'),
        'Phone' => __('Telefón', 'graceart'),
        'Phone (optional)' => __('Telefón (voliteľné)', 'graceart'),
        'Place Order' => __('Objednať s povinnosťou platby', 'graceart'),
        'Place order' => __('Objednať s povinnosťou platby', 'graceart'),
        'Postal code' => __('PSČ', 'graceart'),
        'Postal code (optional)' => __('PSČ (voliteľné)', 'graceart'),
        'Shipping address' => __('Dodacia adresa', 'graceart'),
        'Shipping options' => __('Možnosti dopravy', 'graceart'),
        'State/County' => __('Kraj', 'graceart'),
        'State/County (optional)' => __('Kraj (voliteľné)', 'graceart'),
        'Subtotal' => __('Medzisúčet', 'graceart'),
        'Total' => __('Spolu', 'graceart'),
        'Use same address for billing' => __('Použiť rovnakú adresu pre fakturáciu', 'graceart'),
    ];

    $locale_data = [
        '' => [
            'domain' => 'messages',
            'lang' => function_exists('determine_locale') ? determine_locale() : get_locale(),
        ],
    ];

    foreach ($translations as $source => $translated) {
        $locale_data[$source] = [$translated];
    }

    $script = sprintf(
        '(function(w){if(!w.wp||!w.wp.i18n){return;}var data=%s;w.wp.i18n.setLocaleData(data,"woocommerce");w.wp.i18n.setLocaleData(data,"woo-gutenberg-products-block");})(window);',
        wp_json_encode($locale_data),
    );

    wp_add_inline_script('wp-i18n', $script, 'after');

    $fields = [
        'email' => [
            'label' => __('E-mailová adresa', 'graceart'),
            'optionalLabel' => __('E-mailová adresa (voliteľné)', 'graceart'),
        ],
        'country' => [
            'label' => __('Krajina / región', 'graceart'),
            'optionalLabel' => __('Krajina / región (voliteľné)', 'graceart'),
        ],
        'first_name' => [
            'label' => __('Meno', 'graceart'),
            'optionalLabel' => __('Meno (voliteľné)', 'graceart'),
        ],
        'last_name' => [
            'label' => __('Priezvisko', 'graceart'),
            'optionalLabel' => __('Priezvisko (voliteľné)', 'graceart'),
        ],
        'company' => [
            'label' => __('Spoločnosť', 'graceart'),
            'optionalLabel' => __('Spoločnosť (voliteľné)', 'graceart'),
        ],
        'address_1' => [
            'label' => __('Adresa', 'graceart'),
            'optionalLabel' => __('Adresa (voliteľné)', 'graceart'),
        ],
        'address_2' => [
            'label' => __('Byt, apartmán, jednotka a pod.', 'graceart'),
            'optionalLabel' => __('Byt, apartmán, jednotka a pod. (voliteľné)', 'graceart'),
        ],
        'city' => [
            'label' => __('Mesto', 'graceart'),
            'optionalLabel' => __('Mesto (voliteľné)', 'graceart'),
        ],
        'state' => [
            'label' => __('Kraj', 'graceart'),
            'optionalLabel' => __('Kraj (voliteľné)', 'graceart'),
        ],
        'postcode' => [
            'label' => __('PSČ', 'graceart'),
            'optionalLabel' => __('PSČ (voliteľné)', 'graceart'),
        ],
        'phone' => [
            'label' => __('Telefón', 'graceart'),
            'optionalLabel' => __('Telefón (voliteľné)', 'graceart'),
        ],
    ];

    $field_script = sprintf(
        '(function(w){if(!w.wc||!w.wc.wcSettings||!w.wc.wcSettings.defaultFields){return;}var fields=%s;Object.keys(fields).forEach(function(key){if(w.wc.wcSettings.defaultFields[key]){Object.assign(w.wc.wcSettings.defaultFields[key],fields[key]);}});})(window);',
        wp_json_encode($fields),
    );

    wp_add_inline_script('wc-settings', $field_script, 'after');
});
