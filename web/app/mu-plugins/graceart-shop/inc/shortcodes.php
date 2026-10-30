<?php

/**
 * Company details for page and template texts, from WooCommerce → Settings →
 * General (see company.php), so they are written in one place only.
 */

add_shortcode('graceart_email', fn (): string => esc_html(graceartContactEmail()));

add_shortcode('graceart_email_odkaz', function (): string {
    $email = graceartContactEmail();

    return $email === '' ? '' : sprintf('<a href="mailto:%1$s">%1$s</a>', esc_attr($email));
});

add_shortcode('graceart_ico', fn (): string => esc_html(graceartCompanyIco()));

add_shortcode('graceart_dic', fn (): string => esc_html(graceartCompanyDic()));

add_shortcode('graceart_adresa', function (): string {
    $lines = graceartCompanyAddressLines();

    // The country is left out: the shop's own address needs no "Slovensko".
    if (count($lines) > 1) {
        array_pop($lines);
    }

    return esc_html(implode(', ', $lines));
});

/**
 * "Zapísaná v Živnostenskom registri …". Without the register entry the
 * public sees nothing; a signed-in administrator sees a reminder.
 */
add_shortcode('graceart_register', function (): string {
    $register = graceartCompanyRegister();

    if ($register !== '') {
        return esc_html(sprintf(__('Zapísaná v Živnostenskom registri: %s', 'graceart'), $register));
    }

    return current_user_can('manage_options')
        ? esc_html__('[DOPLNIŤ: živnostenský register – WooCommerce → Nastavenia → Všeobecné]', 'graceart')
        : '';
});

add_shortcode('graceart_rok', fn (): string => esc_html(wp_date('Y')));
