<?php

defined('ABSPATH') || exit;

get_header();

while (have_posts()) :
    the_post();

    global $product;

    if (! $product instanceof WC_Product) {
        continue;
    }

    $gallery_images = graceartProductGalleryImages($product);
    $tabs = apply_filters('woocommerce_product_tabs', []);

    do_action('woocommerce_before_single_product');

    if (post_password_required()) {
        echo get_the_password_form();
        continue;
    }
    ?>

    <div class="page-title-section section" data-bg-image="<?php echo esc_url(fullTemplateUri('assets/images/bg/shop-zapisniky.png')); ?>">
        <div class="container">
            <div class="row">
                <div class="col">
                    <div class="page-title">
                        <h1 class="title"><?php the_title(); ?></h1>
                        <?php graceartWooBreadcrumb(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="product-<?php the_ID(); ?>" <?php wc_product_class('section section-padding border-bottom', $product); ?>>
        <div class="container">
            <div class="row learts-mb-n40">
                <div class="col-lg-6 col-12 learts-mb-40">
                    <div class="product-images">
                        <button class="product-gallery-popup hintT-left" data-hint="<?php esc_attr_e('Kliknite pre zväčšenie', 'graceart'); ?>" data-images="<?php echo esc_attr(graceartProductGalleryPopupImages($gallery_images)); ?>">
                            <i class="fas fa-expand"></i>
                        </button>

                        <div class="product-gallery-slider">
                            <?php foreach ($gallery_images as $image) : ?>
                                <?php if ($image['type'] === 'video') : ?>
                                    <div class="product-video-slide">
                                        <video controls playsinline preload="metadata">
                                            <source src="<?php echo esc_url($image['video_url']); ?>" type="<?php echo esc_attr($image['mime']); ?>">
                                        </video>
                                    </div>
                                <?php else : ?>
                                    <div class="product-zoom" data-image="<?php echo esc_url($image['full']); ?>">
                                        <img src="<?php echo esc_url($image['large']); ?>" alt="<?php echo esc_attr($image['alt']); ?>">
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <?php if (count($gallery_images) > 1) : ?>
                            <div class="product-thumb-slider">
                                <?php foreach ($gallery_images as $image) : ?>
                                    <div class="item<?php echo $image['type'] === 'video' ? ' item-video' : ''; ?>">
                                        <?php if ($image['type'] === 'video') : ?>
                                            <span class="video-thumb-icon"><i class="fas fa-play"></i></span>
                                        <?php else : ?>
                                            <img src="<?php echo esc_url($image['thumb']); ?>" alt="<?php echo esc_attr($image['alt']); ?>">
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-6 col-12 learts-mb-40">
                    <div class="product-summery">
                        <div class="product-ratings">
                            <?php woocommerce_template_single_rating(); ?>
                        </div>

                        <h3 class="product-title"><?php the_title(); ?></h3>

                        <div class="product-price">
                            <?php echo wp_kses_post($product->get_price_html()); ?>
                        </div>

                        <?php if ($product->get_short_description()) : ?>
                            <div class="product-description">
                                <?php echo wp_kses_post(wpautop($product->get_short_description())); ?>
                            </div>
                        <?php endif; ?>

                        <?php woocommerce_template_single_add_to_cart(); ?>

                        <div class="product-meta">
                            <table>
                                <tbody>
                                    <?php if ($product->get_sku()) : ?>
                                        <tr>
                                            <td class="label"><span><?php esc_html_e('Kód produktu', 'graceart'); ?></span></td>
                                            <td class="value"><?php echo esc_html($product->get_sku()); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td class="label"><span><?php esc_html_e('Kategória', 'graceart'); ?></span></td>
                                        <td class="value">
                                            <ul class="product-category">
                                                <?php echo wp_kses_post(wc_get_product_category_list($product->get_id(), '</li><li>', '<li>', '</li>')); ?>
                                            </ul>
                                        </td>
                                    </tr>
                                    <?php if (wc_get_product_tag_list($product->get_id())) : ?>
                                        <tr>
                                            <td class="label"><span><?php esc_html_e('Značky', 'graceart'); ?></span></td>
                                            <td class="value">
                                                <ul class="product-tags">
                                                    <?php echo wp_kses_post(wc_get_product_tag_list($product->get_id(), '</li><li>', '<li>', '</li>')); ?>
                                                </ul>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($tabs) : ?>
        <div class="section section-padding border-bottom">
            <div class="container">
                <ul class="nav product-info-tab-list">
                    <?php $tab_index = 0; ?>
                    <?php foreach ($tabs as $key => $tab) : ?>
                        <li>
                            <a class="<?php echo $tab_index === 0 ? 'active' : ''; ?>" data-bs-toggle="tab" href="#tab-<?php echo esc_attr($key); ?>">
                                <?php echo esc_html($tab['title']); ?>
                            </a>
                        </li>
                        <?php $tab_index++; ?>
                    <?php endforeach; ?>
                </ul>

                <div class="tab-content product-infor-tab-content">
                    <?php $tab_index = 0; ?>
                    <?php foreach ($tabs as $key => $tab) : ?>
                        <div class="tab-pane fade <?php echo $tab_index === 0 ? 'show active' : ''; ?>" id="tab-<?php echo esc_attr($key); ?>">
                            <div class="row">
                                <div class="col-lg-10 col-12 mx-auto">
                                    <?php
                                    if (isset($tab['callback']) && is_callable($tab['callback'])) {
                                        call_user_func($tab['callback'], $key, $tab);
                                    }
                        ?>
                                </div>
                            </div>
                        </div>
                        <?php $tab_index++; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="section section-padding">
        <div class="container">
            <?php woocommerce_output_related_products(); ?>
        </div>
    </div>

    <?php do_action('woocommerce_after_single_product'); ?>

<?php endwhile; ?>

<?php get_footer(); ?>
