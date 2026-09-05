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
                        <?php echo is_shop() ? esc_html__('Grace Art', 'graceart') : woocommerce_page_title(false); ?>
                    </h1>
                    <?php if (is_shop()) : ?>
                        <p class="page-title-subtitle"><?php esc_html_e('Ručne vyrobené kožené zápisníky a fotoalbumy od roku 2016', 'graceart'); ?></p>
                    <?php endif; ?>
                    <?php if (! is_shop() && graceartHasBreadcrumbTrail()) : ?>
                        <?php graceartWooBreadcrumb(); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="section section-padding pt-0">
    <div class="shop-toolbar section-fluid border-bottom">
        <div class="container">
            <div class="row learts-mb-n20">
                <div class="col-md col-12 align-self-center learts-mb-20">
                    <div class="shop-product-filter">
                        <a class="<?php echo is_shop() ? 'active' : ''; ?>" href="<?php echo esc_url(graceartShopUrl()); ?>"><?php esc_html_e('Všetko', 'graceart'); ?></a>
                        <?php $graceart_current_cat = is_product_category() ? (int) get_queried_object_id() : 0; ?>
                        <?php foreach (graceartProductLoopCategoryFilters() as $category_filter) : ?>
                            <a
                                class="<?php echo $graceart_current_cat === $category_filter['term_id'] ? 'active' : ''; ?>"
                                href="<?php echo esc_url($category_filter['url']); ?>"
                            ><?php echo esc_html($category_filter['label']); ?></a>
                        <?php endforeach; ?>
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
                                <button class="toggle hintT-top" data-hint="<?php esc_attr_e('3 stĺpce', 'graceart'); ?>" data-column="3"><i class="ti-layout-grid2-alt"></i></button>
                                <button class="toggle active hintT-top" data-hint="<?php esc_attr_e('4 stĺpce', 'graceart'); ?>" data-column="4"><i class="ti-layout-grid3-alt"></i></button>
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

    <div id="product-filter" class="product-filter section-fluid bg-light">
        <div class="container">
            <div class="row row-cols-lg-3 row-cols-md-3 row-cols-sm-2 row-cols-1 learts-mb-n30">
                <div class="col learts-mb-30">
                    <ul class="widget-list product-filter-widget customScroll">
                        <?php foreach (graceartCatalogOrderingOptions() as $orderby => $label) { ?>
                            <li><a href="<?php echo esc_url(add_query_arg('orderby', $orderby)); ?>"><?php echo esc_html($label); ?></a></li>
                        <?php } ?>
                    </ul>
                </div>

                <div class="col learts-mb-30">
                    <h3 class="widget-title product-filter-widget-title"><?php esc_html_e('Farba', 'graceart'); ?></h3>
                    <ul class="widget-colors product-filter-widget customScroll">
                        <?php foreach (graceartCatalogTerms('pa_farba') as $term) { ?>
                            <li>
                                <a href="<?php echo esc_url(get_term_link($term)); ?>" class="hintT-top" data-hint="<?php echo esc_attr($term->name); ?>">
                                    <span data-bg-color="<?php echo esc_attr(graceartColorHex($term)); ?>"><?php echo esc_html($term->name); ?></span>
                                </a>
                            </li>
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

    <div class="section section-fluid learts-mt-70">
        <div class="container">
            <?php woocommerce_output_all_notices(); ?>

            <?php if (woocommerce_product_loop()) { ?>
                <div id="shop-products" class="products isotope-grid row row-cols-xl-4 row-cols-lg-4 row-cols-md-2 row-cols-sm-2 row-cols-1">
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
