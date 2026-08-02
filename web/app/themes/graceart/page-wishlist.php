<?php

get_header();

graceartPageHero(__('Zoznam prianí', 'graceart'), false);
?>

<div class="section section-padding">
    <div class="container">
        <?php if (shortcode_exists('yith_wcwl_wishlist')) : ?>
            <?php echo do_shortcode('[yith_wcwl_wishlist]'); ?>
        <?php else : ?>
            <form class="cart-form" action="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">
                <table class="cart-wishlist-table table">
                    <thead>
                        <tr>
                            <th class="name" colspan="2"><?php esc_html_e('Produkt', 'graceart'); ?></th>
                            <th class="price"><?php esc_html_e('Cena', 'graceart'); ?></th>
                            <th class="add-to-cart">&nbsp;</th>
                            <th class="remove">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="text-center">
                                <?php esc_html_e('Zoznam prianí je pripravený pre plugin YITH WooCommerce Wishlist. Po jeho aktivácii sa tu zobrazia uložené produkty.', 'graceart'); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="row">
                    <div class="col text-center mb-n3">
                        <a class="btn btn-light btn-hover-dark mr-3 mb-3" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"><?php esc_html_e('Pokračovať v nákupe', 'graceart'); ?></a>
                        <a class="btn btn-dark btn-outline-hover-dark mb-3" href="<?php echo esc_url(graceartCartUrl()); ?>"><?php esc_html_e('Zobraziť košík', 'graceart'); ?></a>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>
