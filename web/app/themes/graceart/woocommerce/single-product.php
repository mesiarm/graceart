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

    // Only the description is shown under the gallery — no tabs, no additional
    // information table, no reviews.
    $graceart_description = trim(apply_filters('the_content', $product->get_description()));

    do_action('woocommerce_before_single_product');

    if (post_password_required()) {
        echo get_the_password_form();
        continue;
    }
    ?>

    <div class="page-title-section section"<?php echo graceartBgImageAttr(graceartBandBackgroundUrl()); ?>>
        <div class="container">
            <div class="row">
                <div class="col">
                    <div class="page-title">
                        <?php /* Branding band, not the page heading — the product name is the <h1>. */ ?>
                        <p class="title page-title-logo">
                            <img src="<?php echo fullTemplateUri('assets/images/logo/logo.jpg'); ?>" width="463" height="100" alt="Grace Art">
                        </p>
                        <?php graceartWooBreadcrumb(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="product-<?php the_ID(); ?>" <?php wc_product_class('section section-padding product-main-section border-bottom', $product); ?>>
        <div class="container">
            <div class="row learts-mb-n40">
                <div class="col-lg-7 col-12 learts-mb-40">
                    <div class="product-images">
                        <button type="button" class="product-gallery-popup hintT-left" data-hint="<?php esc_attr_e('Kliknite pre zväčšenie', 'graceart'); ?>" aria-label="<?php esc_attr_e('Zväčšiť fotografiu', 'graceart'); ?>" data-images="<?php echo esc_attr(graceartProductGalleryPopupImages($gallery_images)); ?>">
                            <i class="fas fa-expand" aria-hidden="true"></i>
                        </button>

                        <div class="product-gallery-slider">
                            <?php foreach ($gallery_images as $graceart_slide_index => $image) : ?>
                                <?php if ($image['type'] === 'video') : ?>
                                    <div class="product-video-slide">
                                        <video controls playsinline preload="metadata">
                                            <source src="<?php echo esc_url($image['video_url']); ?>" type="<?php echo esc_attr($image['mime']); ?>">
                                        </video>
                                    </div>
                                <?php else : ?>
                                    <div class="product-zoom" data-image="<?php echo esc_url($image['full']); ?>">
                                        <?php /* The first slide is the page's largest image: eager, high priority; the rest load on demand. */ ?>
                                        <img src="<?php echo esc_url($image['large']); ?>"<?php echo $image['large_size']; ?> alt="<?php echo esc_attr($image['alt']); ?>"<?php echo $graceart_slide_index === 0 ? ' fetchpriority="high"' : ' loading="lazy"'; ?> decoding="async">
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
                                            <img src="<?php echo esc_url($image['thumb']); ?>"<?php echo $image['thumb_size']; ?> alt="<?php echo esc_attr($image['alt']); ?>" loading="lazy" decoding="async">
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($graceart_description !== '') : ?>
                        <div class="product-gallery-tabs">
                            <div class="product-infor-tab-content"><?php echo wp_kses_post($graceart_description); ?></div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-lg-5 col-12 learts-mb-40">
                    <div class="product-summery">
                        <?php /* Title first, so it lines up with the top of the gallery. */ ?>
                        <h1 class="product-title"><?php the_title(); ?></h1>

                        <?php
                        $graceart_selected_variation_data = graceartResolveSelectedVariationData($product);
    $graceart_price_product = $graceart_selected_variation_data
        ? wc_get_product($graceart_selected_variation_data['variation_id'])
        : $product;
    $graceart_price_product = $graceart_price_product instanceof WC_Product ? $graceart_price_product : $product;
    ?>
                        <div class="product-price">
                            <?php echo wp_kses_post($graceart_price_product->get_price_html()); ?>
                        </div>

                        <p class="product-availability" id="graceart-product-availability">
                            <strong><?php esc_html_e('Dostupnosť:', 'graceart'); ?></strong>
                            <span class="graceart-availability-value"><?php echo esc_html(graceartAvailabilityText($graceart_price_product)); ?></span>
                        </p>

                        <?php if (graceartCardPaymentEnabled()) : ?>
                            <div class="product-payment-info">
                                <span class="product-payment-info__icon">
                                    <img src="<?php echo esc_url(fullTemplateUri('assets/images/payment/visa.svg')); ?>" width="780" height="500" alt="Visa">
                                    <img src="<?php echo esc_url(fullTemplateUri('assets/images/payment/mastercard.svg')); ?>" width="780" height="500" alt="Mastercard">
                                    <img src="<?php echo esc_url(fullTemplateUri('assets/images/payment/googlepay.svg')); ?>" width="512" height="204" alt="Google Pay">
                                    <img src="<?php echo esc_url(fullTemplateUri('assets/images/payment/applepay.svg')); ?>" width="512" height="211" alt="Apple Pay">
                                </span>
                                <span class="product-payment-info__text"><?php esc_html_e('Možná okamžitá platba kartou.', 'graceart'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php woocommerce_template_single_add_to_cart(); ?>

                        <?php $graceart_shipping_by_country = graceartShippingMethodsByCountry(); ?>
                        <?php if ($graceart_shipping_by_country) : ?>
                            <div class="product-shipping-info">
                                <h4 class="product-shipping-info__title"><?php esc_html_e('Cena a spôsob dopravy', 'graceart'); ?></h4>

                                <?php if (count($graceart_shipping_by_country) > 1) : ?>
                                    <div class="product-shipping-info__tabs">
                                        <?php foreach (array_keys($graceart_shipping_by_country) as $graceart_shipping_index => $graceart_country_code) : ?>
                                            <button
                                                type="button"
                                                class="product-shipping-info__tab <?php echo $graceart_shipping_index === 0 ? 'active' : ''; ?>"
                                                data-graceart-shipping-tab="<?php echo esc_attr($graceart_country_code); ?>"
                                            >
                                                <?php echo esc_html($graceart_shipping_by_country[$graceart_country_code]['label']); ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php foreach (array_keys($graceart_shipping_by_country) as $graceart_shipping_index => $graceart_country_code) : ?>
                                    <ul
                                        class="product-shipping-info__list <?php echo $graceart_shipping_index === 0 ? 'active' : ''; ?>"
                                        data-graceart-shipping-panel="<?php echo esc_attr($graceart_country_code); ?>"
                                    >
                                        <?php foreach ($graceart_shipping_by_country[$graceart_country_code]['methods'] as $graceart_shipping_method) : ?>
                                            <li>
                                                <i class="fas fa-truck"></i>
                                                <span><?php echo esc_html($graceart_shipping_method['title']); ?></span>
                                                <?php if ($graceart_shipping_method['cost'] !== '') : ?>
                                                    <strong><?php echo esc_html($graceart_shipping_method['cost']); ?></strong>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php $graceart_free_shipping_min = graceartFreeShippingMinAmount(); ?>
                        <?php if ($graceart_free_shipping_min !== null) : ?>
                            <p class="product-free-shipping">
                                <i class="fas fa-truck"></i>
                                <span>
                                    <?php
                                    echo wp_kses_post(sprintf(
                                        /* translators: %s: formatted minimum order total */
                                        __('Poštovné zdarma pri objednávkach od %s', 'graceart'),
                                        '<strong>' . wp_strip_all_tags(wc_price($graceart_free_shipping_min)) . '</strong>'
                                    ));
                                    ?>
                                </span>
                            </p>
                        <?php endif; ?>

                        <?php if ($product->get_review_count() > 0) : ?>
                            <div class="product-ratings">
                                <?php woocommerce_template_single_rating(); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($product->get_sku() || wc_get_product_tag_list($product->get_id())) : ?>
                            <div class="product-meta">
                                <table>
                                    <tbody>
                                        <?php if ($product->get_sku()) : ?>
                                            <tr>
                                                <td class="label"><span><?php esc_html_e('Kód produktu', 'graceart'); ?></span></td>
                                                <td class="value"><?php echo esc_html($product->get_sku()); ?></td>
                                            </tr>
                                        <?php endif; ?>
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
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section section-padding">
        <div class="container">
            <?php woocommerce_output_related_products(); ?>
        </div>
    </div>

    <?php do_action('woocommerce_after_single_product'); ?>

<?php endwhile; ?>

<?php get_footer(); ?>
