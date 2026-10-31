<?php

/**
 * "Vrátenie tovaru" page helpers: its URL and the pointer to it on the
 * order-received page and in the customer's order e-mails.
 */

/**
 * URL of the "Vrátenie tovaru" page: the refund and returns page chosen in
 * WooCommerce (Settings → Advanced → Page setup), else the home page.
 */
function graceartReturnsUrl(): string
{
    $page_id = (int) get_option('woocommerce_refund_returns_page_id');

    return $page_id > 0 ? (string) get_permalink($page_id) : home_url('/');
}

/**
 * Pointer to the withdrawal information on the order-received page and in the
 * customer's order e-mails, so the right of withdrawal is communicated again
 * with the order confirmation.
 */
function graceartReturnsNoticeText(): string
{
    return sprintf(
        /* translators: %s: link to the returns page */
        __('Od zmluvy môžete odstúpiť do 14 dní od prevzatia tovaru bez udania dôvodu. Postup a vzorový formulár nájdete na stránke %s.', 'graceart'),
        '<a href="' . esc_url(graceartReturnsUrl()) . '">' . esc_html__('Vrátenie tovaru', 'graceart') . '</a>'
    );
}

add_action('woocommerce_thankyou', function (): void {
    echo '<p class="graceart-returns-note">' . wp_kses_post(graceartReturnsNoticeText()) . '</p>';
}, 30);

add_action('woocommerce_email_after_order_table', function ($order, $sent_to_admin, $plain_text): void {
    if ($sent_to_admin) {
        return;
    }

    if ($plain_text) {
        echo "\n" . wp_strip_all_tags(graceartReturnsNoticeText()) . ' ' . esc_url(graceartReturnsUrl()) . "\n";

        return;
    }

    echo '<p>' . wp_kses_post(graceartReturnsNoticeText()) . '</p>';
}, 20, 3);
