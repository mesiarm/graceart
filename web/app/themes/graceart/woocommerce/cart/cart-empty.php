<?php

defined('ABSPATH') || exit;

do_action('woocommerce_cart_is_empty');
?>

<?php if (wc_get_page_id('shop') > 0) : ?>
    <p class="cart-empty woocommerce-info"><?php esc_html_e('Váš košík je momentálne prázdny.', 'graceart'); ?></p>
    <p class="return-to-shop">
        <a class="button wc-backward btn btn-dark btn-outline-hover-dark" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">
            <?php esc_html_e('Späť do obchodu', 'graceart'); ?>
        </a>
    </p>
<?php endif; ?>
