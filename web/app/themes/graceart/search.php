<?php

defined('ABSPATH') || exit;

get_header();
?>

<div class="page-title-section section"<?php echo graceartBgImageAttr(fullTemplateUri('assets/images/bg/shop-zapisniky.png')); ?>>
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="page-title">
                    <h1 class="title">
                        <?php echo esc_html(sprintf(__('Výsledky hľadania: %s', 'graceart'), get_search_query())); ?>
                    </h1>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="section section-padding">
    <div class="container">
        <?php if (have_posts()) : ?>
            <div class="products row row-cols-xl-4 row-cols-lg-3 row-cols-md-2 row-cols-sm-2 row-cols-1">
                <?php while (have_posts()) : the_post(); ?>
                    <?php wc_get_template_part('content', 'product'); ?>
                <?php endwhile; ?>
            </div>

            <?php woocommerce_pagination(); ?>
        <?php else : ?>
            <p><?php esc_html_e('Nenašli sa žiadne produkty vyhovujúce vášmu hľadaniu.', 'graceart'); ?></p>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>
