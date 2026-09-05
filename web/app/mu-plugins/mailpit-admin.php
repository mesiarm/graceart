<?php

/**
 * Plugin Name: Mailpit viewer
 * Description: Shows the captured mail inbox inside the admin. Non-production only.
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Mailpit only runs on the test server, so keep the page out of production.
 */
function graceartMailpitAvailable(): bool
{
    return defined('WP_ENV') && WP_ENV !== 'production';
}

function graceartMailpitMenu(): void
{
    if (! graceartMailpitAvailable()) {
        return;
    }

    add_management_page(
        __('Mailpit', 'graceart'),
        __('Mailpit', 'graceart'),
        'manage_options',
        'graceart-mailpit',
        'graceartMailpitPage',
    );
}
add_action('admin_menu', 'graceartMailpitMenu');

function graceartMailpitPage(): void
{
    $url = home_url('/mailpit/');
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Mailpit', 'graceart'); ?></h1>
        <p class="description">
            <?php esc_html_e('Všetky e-maily z tohto prostredia sú zachytené a neodosielajú sa príjemcom.', 'graceart'); ?>
            <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener">
                <?php esc_html_e('Otvoriť v novom okne', 'graceart'); ?>
            </a>
        </p>
        <iframe
            src="<?php echo esc_url($url); ?>"
            style="width:100%;height:calc(100vh - 220px);min-height:480px;border:1px solid #c3c4c7;background:#fff;"
            title="<?php esc_attr_e('Mailpit', 'graceart'); ?>"></iframe>
    </div>
    <?php
}
