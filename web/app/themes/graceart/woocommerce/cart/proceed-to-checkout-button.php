<?php

defined('ABSPATH') || exit;
?>

<a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="checkout-button button alt wc-forward btn btn-dark btn-outline-hover-dark">
    <?php esc_html_e('Pokračovať k pokladni', 'graceart'); ?>
</a>
