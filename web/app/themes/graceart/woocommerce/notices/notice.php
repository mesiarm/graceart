<?php

defined('ABSPATH') || exit;

if (! $notices) {
    return;
}
?>

<?php foreach ($notices as $notice) : ?>
    <div class="woocommerce-info graceart-woo-notice graceart-woo-notice--info" role="status"<?php echo wc_get_notice_data_attr($notice); ?>>
        <span class="graceart-woo-notice__icon"><i class="far fa-info-circle"></i></span>
        <div class="graceart-woo-notice__content">
            <?php echo wc_kses_notice($notice['notice']); ?>
        </div>
    </div>
<?php endforeach; ?>
