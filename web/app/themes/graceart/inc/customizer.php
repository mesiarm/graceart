<?php

add_action('customize_register', function (WP_Customize_Manager $wp_customize): void {
    $wp_customize->add_section('graceart_social_links', [
        'title' => __('Sociálne siete', 'graceart'),
        'priority' => 160,
    ]);

    $wp_customize->add_setting('graceart_facebook_url', [
        'default' => 'https://www.facebook.com/',
        'sanitize_callback' => 'esc_url_raw',
    ]);

    $wp_customize->add_control('graceart_facebook_url', [
        'label' => __('Facebook URL', 'graceart'),
        'section' => 'graceart_social_links',
        'type' => 'url',
    ]);

    $wp_customize->add_setting('graceart_instagram_url', [
        'default' => 'https://www.instagram.com/',
        'sanitize_callback' => 'esc_url_raw',
    ]);

    $wp_customize->add_control('graceart_instagram_url', [
        'label' => __('Instagram URL', 'graceart'),
        'section' => 'graceart_social_links',
        'type' => 'url',
    ]);

    $wp_customize->add_section('graceart_theme_settings', [
        'title' => __('Nastavenia témy', 'graceart'),
        'priority' => 30,
    ]);

    $wp_customize->add_setting('graceart_band_background', [
        'default' => '',
        'sanitize_callback' => 'absint',
    ]);

    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'graceart_band_background', [
        'label' => __('Pozadie hlavičkového pásu', 'graceart'),
        'description' => __('Fotografia za nadpisom stránky (obchod, produkt, košík, kontakt…). Odporúčaný rozmer 1920 × 800 px.', 'graceart'),
        'section' => 'graceart_theme_settings',
        'mime_type' => 'image',
    ]));

    $wp_customize->add_setting('graceart_footer_copyright', [
        'default' => graceartDefaultFooterCopyright(),
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control('graceart_footer_copyright', [
        'label' => __('Text v pätičke', 'graceart'),
        'description' => __('Rok sa dopĺňa automaticky pred tento text.', 'graceart'),
        'section' => 'graceart_theme_settings',
        'type' => 'text',
    ]);
});

function graceartDefaultFooterCopyright(): string
{
    return __('Grace Art. Všetky práva vyhradené.', 'graceart');
}

function graceartFooterCopyright(): string
{
    $text = (string) get_theme_mod('graceart_footer_copyright', graceartDefaultFooterCopyright());

    return trim($text) !== '' ? $text : graceartDefaultFooterCopyright();
}

/**
 * Background photo of the page-title band; the theme's own image until one
 * is picked in the Customizer.
 */
function graceartBandBackgroundUrl(): string
{
    $image_id = (int) get_theme_mod('graceart_band_background', 0);
    $url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';

    return $url ?: fullTemplateUri('assets/images/bg/shop-zapisniky.png');
}

function graceartFacebookUrl(): string
{
    return (string) get_theme_mod('graceart_facebook_url', 'https://www.facebook.com/GraceArtZapisniky/');
}

function graceartInstagramUrl(): string
{
    return (string) get_theme_mod('graceart_instagram_url', 'https://www.instagram.com/graceart.zapisniky/');
}
