<?php
get_header();

$hero_slides = graceartHomepageHeroSlides((int) get_queried_object_id());
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
            <h3 class="sub-title">Shop now</h3>
            <h2 class="title title-icon-both">Shop our best-sellers</h2>
        </div>
        <!-- Section Title End -->

        <!-- Products Start -->
        <div class="products row row-cols-xl-5 row-cols-lg-4 row-cols-md-3 row-cols-sm-2 row-cols-1">

            <div class="col">
                <div class="product">
                    <div class="product-thumb">
                        <a href="product-details.html" class="image">
                                <span class="product-badges">
                                    <span class="onsale">-13%</span>
                                </span>
                            <img src="<?php echo fullTemplateUri('assets/images/product/s328/product-1.webp'); ?>" alt="Product Image">
                            <img class="image-hover " src="<?php echo fullTemplateUri('assets/images/product/s328/product-1-hover.webp'); ?>" alt="Product Image">
                        </a>
                        <a href="wishlist.html" class="add-to-wishlist hintT-left" data-hint="Add to wishlist"><i class="far fa-heart"></i></a>
                    </div>
                    <div class="product-info">
                        <h6 class="title"><a href="product-details.html">Boho Beard Mug</a></h6>
                        <span class="price">
                                <span class="old">$45.00</span>
                            <span class="new">$39.00</span>
                            </span>
                        <div class="product-buttons">
                            <a href="#quickViewModal" data-bs-toggle="modal" class="product-button hintT-top" data-hint="Quick View"><i class="fas fa-search"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Add to Cart"><i class="fas fa-shopping-cart"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Compare"><i class="fas fa-random"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="product">
                    <div class="product-thumb">
                        <a href="product-details.html" class="image">
                            <img src="<?php echo fullTemplateUri('assets/images/product/s328/product-2.webp'); ?>" alt="Product Image">
                            <img class="image-hover " src="<?php echo fullTemplateUri('assets/images/product/s328/product-2-hover.webp'); ?>" alt="Product Image">
                        </a>
                        <a href="wishlist.html" class="add-to-wishlist hintT-left" data-hint="Add to wishlist"><i class="far fa-heart"></i></a>
                    </div>
                    <div class="product-info">
                        <h6 class="title"><a href="product-details.html">Motorized Tricycle</a></h6>
                        <span class="price">
                                $35.00
                            </span>
                        <div class="product-buttons">
                            <a href="#quickViewModal" data-bs-toggle="modal" class="product-button hintT-top" data-hint="Quick View"><i class="fas fa-search"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Add to Cart"><i class="fas fa-shopping-cart"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Compare"><i class="fas fa-random"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="product">
                    <div class="product-thumb">
                            <span class="product-badges">
                                <span class="hot">hot</span>
                            </span>
                        <a href="product-details.html" class="image">
                            <img src="<?php echo fullTemplateUri('assets/images/product/s328/product-3.webp'); ?>" alt="Product Image">
                            <img class="image-hover " src="<?php echo fullTemplateUri('assets/images/product/s328/product-3-hover.webp'); ?>" alt="Product Image">
                        </a>
                        <a href="wishlist.html" class="add-to-wishlist hintT-left" data-hint="Add to wishlist"><i class="far fa-heart"></i></a>
                    </div>
                    <div class="product-info">
                        <h6 class="title"><a href="product-details.html">Walnut Cutting Board</a></h6>
                        <span class="price">
                                $100.00
                            </span>
                        <div class="product-buttons">
                            <a href="#quickViewModal" data-bs-toggle="modal" class="product-button hintT-top" data-hint="Quick View"><i class="fas fa-search"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Add to Cart"><i class="fas fa-shopping-cart"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Compare"><i class="fas fa-random"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="product">
                    <div class="product-thumb">
                        <a href="product-details.html" class="image">
                                <span class="product-badges">
                                    <span class="onsale">-27%</span>
                                </span>
                            <img src="<?php echo fullTemplateUri('assets/images/product/s328/product-4.webp'); ?>" alt="Product Image">
                            <img class="image-hover " src="<?php echo fullTemplateUri('assets/images/product/s328/product-4-hover.webp'); ?>" alt="Product Image">
                        </a>
                        <a href="wishlist.html" class="add-to-wishlist hintT-left" data-hint="Add to wishlist"><i class="far fa-heart"></i></a>
                    </div>
                    <div class="product-info">
                        <h6 class="title"><a href="product-details.html">Pizza Plate Tray</a></h6>
                        <span class="price">
                                <span class="old">$30.00</span>
                            <span class="new">$22.00</span>
                            </span>
                        <div class="product-buttons">
                            <a href="#quickViewModal" data-bs-toggle="modal" class="product-button hintT-top" data-hint="Quick View"><i class="fas fa-search"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Add to Cart"><i class="fas fa-shopping-cart"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Compare"><i class="fas fa-random"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="product">
                    <div class="product-thumb">
                        <a href="product-details.html" class="image">
                            <img src="<?php echo fullTemplateUri('assets/images/product/s328/product-5.webp'); ?>" alt="Product Image">
                            <img class="image-hover " src="<?php echo fullTemplateUri('assets/images/product/s328/product-5-hover.webp'); ?>" alt="Product Image">
                        </a>
                        <a href="wishlist.html" class="add-to-wishlist hintT-left" data-hint="Add to wishlist"><i class="far fa-heart"></i></a>
                        <div class="product-options">
                            <ul class="colors">
                                <li style="background-color: #c2c2c2;">color one</li>
                                <li style="background-color: #374140;">color two</li>
                                <li style="background-color: #8ea1b2;">color three</li>
                            </ul>
                            <ul class="sizes">
                                <li>Large</li>
                                <li>Medium</li>
                                <li>Small</li>
                            </ul>
                        </div>
                    </div>
                    <div class="product-info">
                        <h6 class="title"><a href="product-details.html">Minimalist Ceramic Pot</a></h6>
                        <span class="price">
                                $120.00
                            </span>
                        <div class="product-buttons">
                            <a href="#quickViewModal" data-bs-toggle="modal" class="product-button hintT-top" data-hint="Quick View"><i class="fas fa-search"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Add to Cart"><i class="fas fa-shopping-cart"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Compare"><i class="fas fa-random"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="product">
                    <div class="product-thumb">
                        <a href="product-details.html" class="image">
                            <img src="<?php echo fullTemplateUri('assets/images/product/s328/product-6.webp'); ?>" alt="Product Image">
                            <img class="image-hover " src="<?php echo fullTemplateUri('assets/images/product/s328/product-6-hover.webp'); ?>" alt="Product Image">
                        </a>
                        <a href="wishlist.html" class="add-to-wishlist hintT-left" data-hint="Add to wishlist"><i class="far fa-heart"></i></a>
                    </div>
                    <div class="product-info">
                        <h6 class="title"><a href="product-details.html">Clear Silicate Teapot</a></h6>
                        <span class="price">
                                $140.00
                            </span>
                        <div class="product-buttons">
                            <a href="#quickViewModal" data-bs-toggle="modal" class="product-button hintT-top" data-hint="Quick View"><i class="fas fa-search"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Add to Cart"><i class="fas fa-shopping-cart"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Compare"><i class="fas fa-random"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="product">
                    <div class="product-thumb">
                        <a href="product-details.html" class="image">
                                <span class="product-badges">
                                    <span class="hot">hot</span>
                                </span>
                            <img src="<?php echo fullTemplateUri('assets/images/product/s328/product-7.webp'); ?>" alt="Product Image">
                            <img class="image-hover " src="<?php echo fullTemplateUri('assets/images/product/s328/product-7-hover.webp'); ?>" alt="Product Image">
                        </a>
                        <a href="wishlist.html" class="add-to-wishlist hintT-left" data-hint="Add to wishlist"><i class="far fa-heart"></i></a>
                    </div>
                    <div class="product-info">
                        <h6 class="title"><a href="product-details.html">Lucky Wooden Elephant</a></h6>
                        <span class="price">
                                $35.00
                            </span>
                        <div class="product-buttons">
                            <a href="#quickViewModal" data-bs-toggle="modal" class="product-button hintT-top" data-hint="Quick View"><i class="fas fa-search"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Add to Cart"><i class="fas fa-shopping-cart"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Compare"><i class="fas fa-random"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="product">
                    <div class="product-thumb">
                        <a href="product-details.html" class="image">
                                <span class="product-badges">
                                    <span class="outofstock"><i class="far fa-frown"></i></span>
                                <span class="hot">hot</span>
                                </span>
                            <img src="<?php echo fullTemplateUri('assets/images/product/s328/product-8.webp'); ?>" alt="Product Image">
                            <img class="image-hover " src="<?php echo fullTemplateUri('assets/images/product/s328/product-8-hover.webp'); ?>" alt="Product Image">
                        </a>
                        <a href="wishlist.html" class="add-to-wishlist hintT-left" data-hint="Add to wishlist"><i class="far fa-heart"></i></a>
                        <div class="product-options">
                            <ul class="colors">
                                <li style="background-color: #000000;">color one</li>
                                <li style="background-color: #b2483c;">color two</li>
                            </ul>
                            <ul class="sizes">
                                <li>Large</li>
                                <li>Medium</li>
                                <li>Small</li>
                            </ul>
                        </div>
                    </div>
                    <div class="product-info">
                        <h6 class="title"><a href="product-details.html">Decorative Christmas Fox</a></h6>
                        <span class="price">
                                $50.00
                            </span>
                        <div class="product-buttons">
                            <a href="#quickViewModal" data-bs-toggle="modal" class="product-button hintT-top" data-hint="Quick View"><i class="fas fa-search"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Add to Cart"><i class="fas fa-shopping-cart"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Compare"><i class="fas fa-random"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="product">
                    <div class="product-thumb">
                        <a href="product-details.html" class="image">
                            <img src="<?php echo fullTemplateUri('assets/images/product/s328/product-9.webp'); ?>" alt="Product Image">
                            <img class="image-hover " src="<?php echo fullTemplateUri('assets/images/product/s328/product-9-hover.webp'); ?>" alt="Product Image">
                        </a>
                        <a href="wishlist.html" class="add-to-wishlist hintT-left" data-hint="Add to wishlist"><i class="far fa-heart"></i></a>
                    </div>
                    <div class="product-info">
                        <h6 class="title"><a href="product-details.html">Aluminum Equestrian</a></h6>
                        <span class="price">
                                $100.00
                            </span>
                        <div class="product-buttons">
                            <a href="#quickViewModal" data-bs-toggle="modal" class="product-button hintT-top" data-hint="Quick View"><i class="fas fa-search"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Add to Cart"><i class="fas fa-shopping-cart"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Compare"><i class="fas fa-random"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="product">
                    <div class="product-thumb">
                        <a href="product-details.html" class="image">
                            <img src="<?php echo fullTemplateUri('assets/images/product/s328/product-10.webp'); ?>" alt="Product Image">
                            <img class="image-hover " src="<?php echo fullTemplateUri('assets/images/product/s328/product-10-hover.webp'); ?>" alt="Product Image">
                        </a>
                        <a href="wishlist.html" class="add-to-wishlist hintT-left" data-hint="Add to wishlist"><i class="far fa-heart"></i></a>
                    </div>
                    <div class="product-info">
                        <h6 class="title"><a href="product-details.html">Fish Cut Out Set</a></h6>
                        <span class="price">
                                $9.00
                            </span>
                        <div class="product-buttons">
                            <a href="#quickViewModal" data-bs-toggle="modal" class="product-button hintT-top" data-hint="Quick View"><i class="fas fa-search"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Add to Cart"><i class="fas fa-shopping-cart"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Compare"><i class="fas fa-random"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="product">
                    <div class="product-thumb">
                        <a href="product-details.html" class="image">
                            <img src="<?php echo fullTemplateUri('assets/images/product/s328/product-11.webp'); ?>" alt="Product Image">
                            <img class="image-hover " src="<?php echo fullTemplateUri('assets/images/product/s328/product-11-hover.webp'); ?>" alt="Product Image">
                        </a>
                        <a href="wishlist.html" class="add-to-wishlist hintT-left" data-hint="Add to wishlist"><i class="far fa-heart"></i></a>
                    </div>
                    <div class="product-info">
                        <h6 class="title"><a href="product-details.html">Electric Egg Blender</a></h6>
                        <span class="price">
                                $200.00
                            </span>
                        <div class="product-buttons">
                            <a href="#quickViewModal" data-bs-toggle="modal" class="product-button hintT-top" data-hint="Quick View"><i class="fas fa-search"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Add to Cart"><i class="fas fa-shopping-cart"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Compare"><i class="fas fa-random"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="product">
                    <div class="product-thumb">
                        <a href="product-details.html" class="image">
                            <img src="<?php echo fullTemplateUri('assets/images/product/s328/product-12.webp'); ?>" alt="Product Image">
                            <img class="image-hover " src="<?php echo fullTemplateUri('assets/images/product/s328/product-12-hover.webp'); ?>" alt="Product Image">
                        </a>
                        <a href="wishlist.html" class="add-to-wishlist hintT-left" data-hint="Add to wishlist"><i class="far fa-heart"></i></a>
                    </div>
                    <div class="product-info">
                        <h6 class="title"><a href="product-details.html">Cape Cottage Playhouse</a></h6>
                        <span class="price">
                                $35.00
                            </span>
                        <div class="product-buttons">
                            <a href="#quickViewModal" data-bs-toggle="modal" class="product-button hintT-top" data-hint="Quick View"><i class="fas fa-search"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Add to Cart"><i class="fas fa-shopping-cart"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Compare"><i class="fas fa-random"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="product">
                    <div class="product-thumb">
                        <a href="product-details.html" class="image">
                            <img src="<?php echo fullTemplateUri('assets/images/product/s328/product-13.webp'); ?>" alt="Product Image">
                            <img class="image-hover " src="<?php echo fullTemplateUri('assets/images/product/s328/product-13-hover.webp'); ?>" alt="Product Image">
                        </a>
                        <a href="wishlist.html" class="add-to-wishlist hintT-left" data-hint="Add to wishlist"><i class="far fa-heart"></i></a>
                        <div class="product-options">
                            <ul class="colors">
                                <li style="background-color: #ffffff;">color one</li>
                                <li style="background-color: #01796f;">color two</li>
                            </ul>
                        </div>
                    </div>
                    <div class="product-info">
                        <h6 class="title"><a href="product-details.html">Kernel Popcorn Bowl</a></h6>
                        <span class="price">
                                $25.00
                            </span>
                        <div class="product-buttons">
                            <a href="#quickViewModal" data-bs-toggle="modal" class="product-button hintT-top" data-hint="Quick View"><i class="fas fa-search"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Add to Cart"><i class="fas fa-shopping-cart"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Compare"><i class="fas fa-random"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="product">
                    <div class="product-thumb">
                        <a href="product-details.html" class="image">
                                <span class="product-badges">
                                    <span class="outofstock"><i class="far fa-frown"></i></span>
                                </span>
                            <img src="<?php echo fullTemplateUri('assets/images/product/s328/product-14.webp'); ?>" alt="Product Image">
                            <img class="image-hover " src="<?php echo fullTemplateUri('assets/images/product/s328/product-14-hover.webp'); ?>" alt="Product Image">
                        </a>
                        <a href="wishlist.html" class="add-to-wishlist hintT-left" data-hint="Add to wishlist"><i class="far fa-heart"></i></a>
                        <div class="product-options">
                            <ul class="colors">
                                <li style="background-color: #000000;">color one</li>
                                <li style="background-color: #ffffff;">color two</li>
                            </ul>
                        </div>
                    </div>
                    <div class="product-info">
                        <h6 class="title"><a href="product-details.html">Abstract Folded Pots</a></h6>
                        <span class="price">
                                $50.00 - $55.00
                            </span>
                        <div class="product-buttons">
                            <a href="#quickViewModal" data-bs-toggle="modal" class="product-button hintT-top" data-hint="Quick View"><i class="fas fa-search"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Add to Cart"><i class="fas fa-shopping-cart"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Compare"><i class="fas fa-random"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="product">
                    <div class="product-thumb">
                        <a href="product-details.html" class="image">
                            <img src="<?php echo fullTemplateUri('assets/images/product/s328/product-15.webp'); ?>" alt="Product Image">
                            <img class="image-hover " src="<?php echo fullTemplateUri('assets/images/product/s328/product-15-hover.webp'); ?>" alt="Product Image">
                        </a>
                        <a href="wishlist.html" class="add-to-wishlist hintT-left" data-hint="Add to wishlist"><i class="far fa-heart"></i></a>
                    </div>
                    <div class="product-info">
                        <h6 class="title"><a href="product-details.html">Brush & Dustpan Set</a></h6>
                        <span class="price">
                                $9.00
                            </span>
                        <div class="product-buttons">
                            <a href="#quickViewModal" data-bs-toggle="modal" class="product-button hintT-top" data-hint="Quick View"><i class="fas fa-search"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Add to Cart"><i class="fas fa-shopping-cart"></i></a>
                            <a href="#" class="product-button hintT-top" data-hint="Compare"><i class="fas fa-random"></i></a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- Products End -->

    </div>
</div>
<?php get_footer();
