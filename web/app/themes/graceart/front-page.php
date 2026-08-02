<?php
get_header();

$hero_slides = graceartHomepageHeroSlides((int) get_queried_object_id());
$bestseller_ids = graceartHomepageBestsellerIds((int) get_queried_object_id());
?>
<!-- Slider main container Start -->
<div class="home1-slider swiper-container">
    <div class="swiper-wrapper">
        <?php foreach ($hero_slides as $slide) : ?>
            <div class="home1-slide-item swiper-slide" data-bg-image="<?php echo esc_url($slide['image']); ?>">
                <div class="home1-slide1-content">
                    <span class="bg"></span>
                    <span class="slide-border"></span>
                    <?php if ($slide['title']) : ?>
                        <h2 class="title"><?php echo esc_html($slide['title']); ?></h2>
                    <?php endif; ?>
                    <?php if ($slide['subtitle']) : ?>
                        <h3 class="sub-title"><?php echo esc_html($slide['subtitle']); ?></h3>
                    <?php endif; ?>
                    <?php if ($slide['button_text'] && $slide['button_url']) : ?>
                        <div class="link"><a href="<?php echo esc_url($slide['button_url']); ?>"><?php echo esc_html($slide['button_text']); ?></a></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="home1-slider-prev swiper-button-prev"><i class="ti-angle-left"></i></div>
    <div class="home1-slider-next swiper-button-next"><i class="ti-angle-right"></i></div>
</div>
<!-- Slider main container End -->

<!-- Category Banner Section Start -->
<div class="section section-fluid section-padding">
    <div class="container">
        <div class="category-banner1-carousel">
            <?php foreach (graceartHomepageCategoryBanners((int) get_queried_object_id()) as $banner): ?>
                <div class="col">
                    <div class="category-banner1">
                        <div class="inner">
                            <a href="<?php echo esc_url($banner['url']); ?>" class="image">
                                <img src="<?php echo esc_url($banner['image']); ?>" alt="<?php echo esc_attr($banner['label']); ?>">
                            </a>
                            <div class="content">
                                <h3 class="title">
                                    <a href="<?php echo esc_url($banner['url']); ?>"><?php echo esc_html($banner['label']); ?></a>
                                    <span class="number"><?php echo esc_html((string) $banner['count']); ?></span>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </div>
</div>
<!-- Category Banner Section End -->

<!-- Product Section Start -->
<div class="section section-fluid section-padding pt-0">
    <div class="container">

        <!-- Section Title Start -->
        <div class="section-title text-center">
            <h3 class="sub-title"><?php esc_html_e('Nakupujte teraz', 'graceart'); ?></h3>
            <h2 class="title title-icon-both"><?php esc_html_e('Naše najpredávanejšie produkty', 'graceart'); ?></h2>
        </div>
        <!-- Section Title End -->

        <!-- Products Start -->
        <div class="products row row-cols-xl-5 row-cols-lg-4 row-cols-md-3 row-cols-sm-2 row-cols-1">
            <?php
            if ($bestseller_ids) :
                $bestsellers_query = new WP_Query([
                    'post_type' => 'product',
                    'post__in' => $bestseller_ids,
                    'orderby' => 'post__in',
                    'posts_per_page' => count($bestseller_ids),
                ]);

                while ($bestsellers_query->have_posts()) :
                    $bestsellers_query->the_post();

                    global $product;
                    $product = wc_get_product(get_the_ID());

                    wc_get_template_part('content', 'product');
                endwhile;

                wp_reset_postdata();
            endif;
?>
        </div>
        <!-- Products End -->

    </div>
</div>
<?php get_footer();
