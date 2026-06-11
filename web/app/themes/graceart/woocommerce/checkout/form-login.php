<?php

defined('ABSPATH') || exit;

$registration_at_checkout = WC_Checkout::instance()->is_registration_enabled();
$login_reminder_at_checkout = 'yes' === get_option('woocommerce_enable_checkout_login_reminder');

if (is_user_logged_in()) :
    return;
endif;
?>

<?php if ($login_reminder_at_checkout) : ?>
    <div class="woocommerce-form-login-toggle">
        <?php
        wc_print_notice(
            apply_filters('woocommerce_checkout_login_message', esc_html__('Už ste u nás nakupovali?', 'graceart')) . ' <a href="#" class="showlogin">' . esc_html__('Kliknite sem a prihláste sa', 'graceart') . '</a>',
            'notice',
        );
    ?>
    </div>
<?php endif; ?>

<?php
if ($registration_at_checkout || $login_reminder_at_checkout) :
    $show_form = isset($_POST['login']); // phpcs:ignore WordPress.Security.NonceVerification.Missing

    woocommerce_login_form([
        'message' => esc_html__('Ak ste u nás už nakupovali, zadajte svoje údaje nižšie. Ak ste nový zákazník, pokračujte prosím k fakturačným údajom.', 'graceart'),
        'redirect' => wc_get_checkout_url(),
        'hidden' => ! $show_form,
    ]);
endif;
