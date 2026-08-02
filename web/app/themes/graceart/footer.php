<div class="footer2-section section section-padding">
    <div class="container">
        <div class="row learts-mb-n40">

            <div class="col-lg-4 learts-mb-40">
                <div class="widget-about">
                    <img src="<?php echo fullTemplateUri('assets/images/logo/logo.svg'); ?>" alt="Grace Art Logo">
                </div>
            </div>

            <div class="col-lg-4 learts-mb-40">
                <div class="row">
                    <div class="col">
                        <ul class="widget-list">
                            <li><a href="<?php echo esc_url(graceartShopUrl()); ?>"><?php esc_html_e('Obchod', 'graceart'); ?></a></li>
                            <?php $graceart_about_page = get_page_by_path('o-nas'); ?>
                            <?php if ($graceart_about_page instanceof WP_Post) : ?>
                                <li><a href="<?php echo esc_url(get_permalink($graceart_about_page)); ?>"><?php esc_html_e('O nás', 'graceart'); ?></a></li>
                            <?php endif; ?>
                            <?php $graceart_contact_page = get_page_by_path('kontakt'); ?>
                            <?php if ($graceart_contact_page instanceof WP_Post) : ?>
                                <li><a href="<?php echo esc_url(get_permalink($graceart_contact_page)); ?>"><?php esc_html_e('Kontaktujte nás', 'graceart'); ?></a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="col">
                        <ul class="widget-list">
                            <li><a href="<?php echo esc_url(graceartMyAccountUrl()); ?>"><?php esc_html_e('Môj účet', 'graceart'); ?></a></li>
                            <li><a href="<?php echo esc_url(graceartCartUrl()); ?>"><?php esc_html_e('Košík', 'graceart'); ?></a></li>
                            <li><a href="<?php echo esc_url(graceartCheckoutUrl()); ?>"><?php esc_html_e('Pokladňa', 'graceart'); ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 learts-mb-40">
                <ul class="widget-list">
                    <li> <i class="fab fa-twitter"></i> <a href="https://www.twitter.com/">Twitter</a></li>
                    <li> <i class="fab fa-facebook-f"></i> <a href="https://www.facebook.com/">Facebook</a></li>
                    <li> <i class="fab fa-instagram"></i> <a href="https://www.instagram.com/">Instagram</a></li>
                    <li> <i class="fab fa-youtube"></i> <a href="https://www.youtube.com/">Youtube</a></li>
                </ul>
            </div>

        </div>
    </div>
</div>

<div class="footer2-copyright section">
    <div class="container">
        <p class="copyright text-center">&copy; <?php echo esc_html(gmdate('Y')); ?> Graceart. <?php esc_html_e('Všetky práva vyhradené.', 'graceart'); ?></p>
    </div>
</div>
<!-- Modal -->
<div class="quickViewModal modal fade" id="quickViewModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <button class="close" data-bs-dismiss="modal">&times;</button>
            <div class="row learts-mb-n30">

                <!-- Product Images Start -->
                <div class="col-lg-6 col-12 learts-mb-30">
                    <div class="product-images">
                        <div class="product-gallery-slider-quickview">
                            <div class="product-zoom" data-image="<?php echo fullTemplateUri('assets/images/product/single/1/product-zoom-1.webp'); ?>">
                                <img src="<?php echo fullTemplateUri('assets/images/product/single/1/product-1.webp'); ?>" alt="">
                            </div>
                            <div class="product-zoom" data-image="<?php echo fullTemplateUri('assets/images/product/single/1/product-zoom-2.webp'); ?>">
                                <img src="<?php echo fullTemplateUri('assets/images/product/single/1/product-2.webp'); ?>" alt="">
                            </div>
                            <div class="product-zoom" data-image="<?php echo fullTemplateUri('assets/images/product/single/1/product-zoom-3.webp'); ?>">
                                <img src="<?php echo fullTemplateUri('assets/images/product/single/1/product-3.webp'); ?>" alt="">
                            </div>
                            <div class="product-zoom" data-image="<?php echo fullTemplateUri('assets/images/product/single/1/product-zoom-4.webp'); ?>">
                                <img src="<?php echo fullTemplateUri('assets/images/product/single/1/product-4.webp'); ?>" alt="">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Product Images End -->

                <!-- Product Summery Start -->
                <div class="col-lg-6 col-12 overflow-hidden position-relative learts-mb-30">
                    <div class="product-summery customScroll">
                        <div class="product-ratings">
                                <span class="star-rating">
                                <span class="rating-active" style="width: 100%;">ratings</span>
                                </span>
                            <a href="#reviews" class="review-link">(<span class="count">3</span> customer reviews)</a>
                        </div>
                        <h3 class="product-title">Cleaning Dustpan & Brush</h3>
                        <div class="product-price">£38.00 – £50.00</div>
                        <div class="product-description">
                            <p>Easy clip-on handle – Hold the brush and dustpan together for storage; the dustpan edge is serrated to allow easy scraping off the hair without entanglement. High-quality bristles – no burr damage, no scratches, thick and durable, comfortable to remove dust and smaller particles.</p>
                        </div>
                        <div class="product-variations">
                            <table>
                                <tbody>
                                <tr>
                                    <td class="label"><span>Size</span></td>
                                    <td class="value">
                                        <div class="product-sizes">
                                            <a href="#">Large</a>
                                            <a href="#">Medium</a>
                                            <a href="#">Small</a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><span>Color</span></td>
                                    <td class="value">
                                        <div class="product-colors">
                                            <a href="#" data-bg-color="#000000"></a>
                                            <a href="#" data-bg-color="#ffffff"></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><span>Quantity</span></td>
                                    <td class="value">
                                        <div class="product-quantity">
                                            <span class="qty-btn minus"><i class="ti-minus"></i></span>
                                            <input type="text" class="input-qty" value="1">
                                            <span class="qty-btn plus"><i class="ti-plus"></i></span>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="product-buttons">
                            <a href="#" class="btn btn-icon btn-outline-body btn-hover-dark"><i class="far fa-heart"></i></a>
                            <a href="#" class="btn btn-dark btn-outline-hover-dark"><i class="fas fa-shopping-cart"></i> Add to Cart</a>
                            <a href="#" class="btn btn-icon btn-outline-body btn-hover-dark"><i class="fas fa-random"></i></a>
                        </div>
                        <div class="product-brands">
                            <span class="title">Brands</span>
                            <div class="brands">
                                <a href="#"><img src="<?php echo fullTemplateUri('assets/images/brands/brand-3.webp'); ?>" alt=""></a>
                                <a href="#"><img src="<?php echo fullTemplateUri('assets/images/brands/brand-8.webp'); ?>" alt=""></a>
                            </div>
                        </div>
                        <div class="product-meta mb-0">
                            <table>
                                <tbody>
                                <tr>
                                    <td class="label"><span>SKU</span></td>
                                    <td class="value">0404019</td>
                                </tr>
                                <tr>
                                    <td class="label"><span>Category</span></td>
                                    <td class="value">
                                        <ul class="product-category">
                                            <li><a href="#">Kitchen</a></li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><span>Tags</span></td>
                                    <td class="value">
                                        <ul class="product-tags">
                                            <li><a href="#">handmade</a></li>
                                            <li><a href="#">learts</a></li>
                                            <li><a href="#">mug</a></li>
                                            <li><a href="#">product</a></li>
                                            <li><a href="#">learts</a></li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><span>Share on</span></td>
                                    <td class="va">
                                        <div class="product-share">
                                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                                            <a href="#"><i class="fab fa-twitter"></i></a>
                                            <a href="#"><i class="fab fa-google-plus-g"></i></a>
                                            <a href="#"><i class="fab fa-pinterest"></i></a>
                                            <a href="#"><i class="far fa-envelope"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Product Summery End -->

            </div>
        </div>
    </div>
</div>

<?php wp_footer(); ?>

</body>

</html>
