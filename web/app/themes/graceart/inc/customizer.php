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
});

function graceartFacebookUrl(): string
{
    return (string) get_theme_mod('graceart_facebook_url', 'https://www.facebook.com/');
}

function graceartInstagramUrl(): string
{
    return (string) get_theme_mod('graceart_instagram_url', 'https://www.instagram.com/');
}
