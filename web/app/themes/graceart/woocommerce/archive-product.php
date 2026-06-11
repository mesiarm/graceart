<?php

defined('ABSPATH') || exit;

get_header();
?>

<div class="page-title-section section" data-bg-image="<?php echo esc_url(fullTemplateUri('assets/images/bg/shop-zapisniky.png')); ?>">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="page-title">
                    <h1 class="title">
                        <?php echo is_shop() ? esc_html__('Obchod', 'graceart') : woocommerce_page_title(false); ?>
                    </h1>
                    <?php graceartWooBreadcrumb(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="section section-padding pt-0">
    <div class="shop-toolbar border-bottom">
        <div class="container">
            <div class="row learts-mb-n20">
                <div class="col-md col-12 align-self-center learts-mb-20">
                    <div class="isotope-filter shop-product-filter" data-target="#shop-products">
                        <button class="active" data-filter="*"><?php esc_html_e('Všetko', 'graceart'); ?></button>
                        <button data-filter=".featured"><?php esc_html_e('Odporúčané', 'graceart'); ?></button>
                        <button data-filter=".new"><?php esc_html_e('Novinky', 'graceart'); ?></button>
                        <button data-filter=".sales"><?php esc_html_e('Zľavnené', 'graceart'); ?></button>
                    </div>
                </div>

                <div class="col-md-auto col-12 learts-mb-20">
                    <ul class="shop-toolbar-controls">
                        <li>
                            <div class="product-sorting">
                                <?php woocommerce_catalog_ordering(); ?>
                            </div>
                        </li>
                        <li>
                            <div class="product-column-toggle d-none d-xl-flex">
                                <button class="toggle active hintT-top" data-hint="<?php esc_attr_e('5 stĺpcov', 'graceart'); ?>" data-column="5"><i class="ti-layout-grid4-alt"></i></button>
                                <button class="toggle hintT-top" data-hint="<?php esc_attr_e('4 stĺpce', 'graceart'); ?>" data-column="4"><i class="ti-layout-grid3-alt"></i></button>
                                <button class="toggle hintT-top" data-hint="<?php esc_attr_e('3 stĺpce', 'graceart'); ?>" data-column="3"><i class="ti-layout-grid2-alt"></i></button>
                            </div>
                        </li>
                        <li>
                            <a class="product-filter-toggle" href="#product-filter"><?php esc_html_e('Filtre', 'graceart'); ?></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div id="product-filter" class="product-filter bg-light">
        <div class="container">
            <div class="row row-cols-lg-5 row-cols-md-3 row-cols-sm-2 row-cols-1 learts-mb-n30">
                <div class="col learts-mb-30">
                    <h3 class="widget-title product-filter-widget-title"><?php esc_html_e('Zoradiť podľa', 'graceart'); ?></h3>
                    <ul class="widget-list product-filter-widget customScroll">
                        <?php foreach (graceartCatalogOrderingOptions() as $orderby => $label) { ?>
                            <li><a href="<?php echo esc_url(add_query_arg('orderby', $orderby)); ?>"><?php echo esc_html($label); ?></a></li>
                        <?php } ?>
                    </ul>
                </div>

                <div class="col learts-mb-30">
                    <h3 class="widget-title product-filter-widget-title"><?php esc_html_e('Kategórie', 'graceart'); ?></h3>
                    <ul class="widget-list product-filter-widget customScroll">
                        <?php
                        wp_list_categories([
                            'taxonomy' => 'product_cat',
                            'title_li' => '',
                            'show_count' => true,
                            'hide_empty' => true,
                        ]);
?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="section learts-mt-70">
        <div class="container">
            <?php woocommerce_output_all_notices(); ?>

            <?php if (woocommerce_product_loop()) { ?>
                <?php woocommerce_result_count(); ?>

                <div id="shop-products" class="products isotope-grid row row-cols-xl-5 row-cols-lg-4 row-cols-md-3 row-cols-sm-2 row-cols-1">
                    <div class="grid-sizer col-1"></div>

                    <?php while (have_posts()) { ?>
                        <?php the_post(); ?>
                        <?php do_action('woocommerce_shop_loop'); ?>
                        <?php wc_get_template_part('content', 'product'); ?>
                    <?php } ?>
                </div>

                <?php woocommerce_pagination(); ?>
            <?php } else { ?>
                <?php do_action('woocommerce_no_products_found'); ?>
            <?php } ?>
        </div>
    </div>
</div>

<?php
get_footer();
