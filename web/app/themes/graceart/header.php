<!DOCTYPE html>
<html class="no-js" <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo fullTemplateUri('assets/images/favicon.webp'); ?>">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<!-- Header Section Start -->
<div class="header-section section bg-white d-none d-xl-block">
    <div class="container">
        <div class="row row-cols-lg-3 align-items-center">
            <div class="col"></div>
            <div class="col">
                <div class="header-logo justify-content-center">
                    <a href="<?php echo home_url(); ?>">
                        <img src="<?php echo fullTemplateUri('assets/images/logo/logo.jpg'); ?>" alt="Grace Art Logo">
                    </a>
                </div>
            </div>
            <!-- Header Logo End -->

            <!-- Header Tools Start -->
            <div class="col">
                <div class="header-tools justify-content-end">
                    <div class="header-login">
                        <a href="<?php echo esc_url(graceartMyAccountUrl()); ?>"><i class="far fa-user"></i></a>
                    </div>
                    <div class="header-search">
                        <a href="#offcanvas-search" class="offcanvas-toggle"><i class="fas fa-search"></i></a>
                    </div>
                    <div class="header-wishlist">
                        <a href="<?php echo esc_url(graceartWishlistUrl()); ?>"><span class="wishlist-count"><?php echo esc_html((string) graceartWishlistCount()); ?></span><i class="far fa-heart"></i></a>
                    </div>
                    <div class="header-cart">
                        <a href="<?php echo esc_url(graceartCartUrl()); ?>"><span class="cart-count"><?php echo esc_html((string) graceartCartCount()); ?></span><i class="fas fa-shopping-cart"></i></a>
                    </div>
                </div>
            </div>
            <!-- Header Tools End -->

        </div>
    </div>

    <!-- Site Menu Section Start -->
    <div class="site-menu-section section">
        <div class="container">
            <?php graceartHeaderMenu(); ?>
        </div>
    </div>
    <!-- Site Menu Section End -->

</div>
<!-- Header Section End -->

<!-- Header Sticky Section Start -->
<div class="sticky-header header-menu-center section bg-white d-none d-xl-block">
    <div class="container">
        <div class="row align-items-center">

            <!-- Header Logo Start -->
            <div class="col">
                <div class="header-logo">
                    <a href="<?php echo home_url(); ?>">
                        <img src="<?php echo fullTemplateUri('assets/images/logo/logo.jpg'); ?>" alt="Grace Art Logo">
                    </a>
                </div>
            </div>
            <!-- Header Logo End -->

            <!-- Search Start -->
            <div class="col d-none d-xl-block">
                <?php graceartHeaderMenu(); ?>
            </div>
            <!-- Search End -->

            <!-- Header Tools Start -->
            <div class="col-auto">
                <div class="header-tools justify-content-end">
                    <div class="header-login">
                        <a href="<?php echo esc_url(graceartMyAccountUrl()); ?>"><i class="far fa-user"></i></a>
                    </div>
                    <div class="header-search d-none d-sm-block">
                        <a href="#offcanvas-search" class="offcanvas-toggle"><i class="fas fa-search"></i></a>
                    </div>
                    <div class="header-wishlist">
                        <a href="<?php echo esc_url(graceartWishlistUrl()); ?>"><span class="wishlist-count"><?php echo esc_html((string) graceartWishlistCount()); ?></span><i class="far fa-heart"></i></a>
                    </div>
                    <div class="header-cart">
                        <a href="<?php echo esc_url(graceartCartUrl()); ?>"><span class="cart-count"><?php echo esc_html((string) graceartCartCount()); ?></span><i class="fas fa-shopping-cart"></i></a>
                    </div>
                    <div class="mobile-menu-toggle d-xl-none">
                        <a href="#offcanvas-mobile-menu" class="offcanvas-toggle">
                            <svg viewBox="0 0 800 600">
                                <path d="M300,220 C300,220 520,220 540,220 C740,220 640,540 520,420 C440,340 300,200 300,200" class="top"></path>
                                <path d="M300,320 L540,320" class="middle"></path>
                                <path d="M300,210 C300,210 520,210 540,210 C740,210 640,530 520,410 C440,330 300,190 300,190" class="bottom" transform="translate(480, 320) scale(1, -1) translate(-480, -318) "></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <!-- Header Tools End -->

        </div>
    </div>

</div>
<!-- Header Sticky Section End -->
<!-- Mobile Header Section Start -->
<div class="mobile-header bg-white section d-xl-none">
    <div class="container">
        <div class="row align-items-center">

            <!-- Header Logo Start -->
            <div class="col">
                <div class="header-logo">
                    <a href="<?php echo home_url(); ?>">
                        <img src="<?php echo fullTemplateUri('assets/images/logo/logo.png'); ?>" alt="Grace Art Logo">
                    </a>
                </div>
            </div>
            <!-- Header Logo End -->

            <!-- Header Tools Start -->
            <div class="col-auto">
                <div class="header-tools justify-content-end">
                    <div class="header-login d-none d-sm-block">
                        <a href="<?php echo esc_url(graceartMyAccountUrl()); ?>"><i class="far fa-user"></i></a>
                    </div>
                    <div class="header-search d-none d-sm-block">
                        <a href="#offcanvas-search" class="offcanvas-toggle"><i class="fas fa-search"></i></a>
                    </div>
                    <div class="header-wishlist d-none d-sm-block">
                        <a href="<?php echo esc_url(graceartWishlistUrl()); ?>"><span class="wishlist-count"><?php echo esc_html((string) graceartWishlistCount()); ?></span><i class="far fa-heart"></i></a>
                    </div>
                    <div class="header-cart">
                        <a href="<?php echo esc_url(graceartCartUrl()); ?>"><span class="cart-count"><?php echo esc_html((string) graceartCartCount()); ?></span><i class="fas fa-shopping-cart"></i></a>
                    </div>
                    <div class="mobile-menu-toggle">
                        <a href="#offcanvas-mobile-menu" class="offcanvas-toggle">
                            <svg viewBox="0 0 800 600">
                                <path d="M300,220 C300,220 520,220 540,220 C740,220 640,540 520,420 C440,340 300,200 300,200" class="top"></path>
                                <path d="M300,320 L540,320" class="middle"></path>
                                <path d="M300,210 C300,210 520,210 540,210 C740,210 640,530 520,410 C440,330 300,190 300,190" class="bottom" transform="translate(480, 320) scale(1, -1) translate(-480, -318) "></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <!-- Header Tools End -->

        </div>
    </div>
</div>
<!-- Mobile Header Section End -->

<!-- Mobile Header Section Start -->
<div class="mobile-header sticky-header bg-white section d-xl-none">
    <div class="container">
        <div class="row align-items-center">

            <!-- Header Logo Start -->
            <div class="col">
                <div class="header-logo">
                    <a href="<?php echo home_url(); ?>">
                        <img src="<?php echo fullTemplateUri('assets/images/logo/logo.png'); ?>" alt="Grace Art Logo">
                    </a>
                </div>
            </div>
            <!-- Header Logo End -->

            <!-- Header Tools Start -->
            <div class="col-auto">
                <div class="header-tools justify-content-end">
                    <div class="header-login d-none d-sm-block">
                        <a href="<?php echo esc_url(graceartMyAccountUrl()); ?>"><i class="far fa-user"></i></a>
                    </div>
                    <div class="header-search d-none d-sm-block">
                        <a href="#offcanvas-search" class="offcanvas-toggle"><i class="fas fa-search"></i></a>
                    </div>
                    <div class="header-wishlist d-none d-sm-block">
                        <a href="<?php echo esc_url(graceartWishlistUrl()); ?>"><span class="wishlist-count"><?php echo esc_html((string) graceartWishlistCount()); ?></span><i class="far fa-heart"></i></a>
                    </div>
                    <div class="header-cart">
                        <a href="<?php echo esc_url(graceartCartUrl()); ?>"><span class="cart-count"><?php echo esc_html((string) graceartCartCount()); ?></span><i class="fas fa-shopping-cart"></i></a>
                    </div>
                    <div class="mobile-menu-toggle">
                        <a href="#offcanvas-mobile-menu" class="offcanvas-toggle">
                            <svg viewBox="0 0 800 600">
                                <path d="M300,220 C300,220 520,220 540,220 C740,220 640,540 520,420 C440,340 300,200 300,200" class="top"></path>
                                <path d="M300,320 L540,320" class="middle"></path>
                                <path d="M300,210 C300,210 520,210 540,210 C740,210 640,530 520,410 C440,330 300,190 300,190" class="bottom" transform="translate(480, 320) scale(1, -1) translate(-480, -318) "></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <!-- Header Tools End -->

        </div>
    </div>
</div>
<!-- Mobile Header Section End -->
<!-- OffCanvas Search Start -->
<div id="offcanvas-search" class="offcanvas offcanvas-search">
    <div class="inner">
        <div class="offcanvas-search-form">
            <button class="offcanvas-close">×</button>
            <form action="#">
                <div class="row mb-n3">
                    <div class="col-lg-8 col-12 mb-3"><input type="text" placeholder="Search Products..."></div>
                    <div class="col-lg-4 col-12 mb-3">
                        <select class="search-select select2-basic">
                            <option value="0">All Categories</option>
                            <option value="kids-babies">Kids &amp; Babies</option>
                            <option value="home-decor">Home Decor</option>
                            <option value="gift-ideas">Gift ideas</option>
                            <option value="kitchen">Kitchen</option>
                            <option value="toys">Toys</option>
                            <option value="kniting-sewing">Kniting &amp; Sewing</option>
                            <option value="pots">Pots</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <p class="search-description text-body-light mt-2"> <span># Type at least 1 character to search</span> <span># Hit enter to search or ESC to close</span></p>

    </div>
</div>
<!-- OffCanvas Search End -->

<!-- OffCanvas Wishlist Start -->
<div id="offcanvas-wishlist" class="offcanvas offcanvas-wishlist">
    <div class="inner">
        <div class="head">
            <span class="title">Wishlist</span>
            <button class="offcanvas-close">×</button>
        </div>
        <div class="body customScroll">
            <ul class="minicart-product-list">
                <li>
                    <a href="product-details.html" class="image"><img src="<?php echo fullTemplateUri('assets/images/product/cart-product-1.webp'); ?>" alt="Cart product Image"></a>
                    <div class="content">
                        <a href="product-details.html" class="title">Walnut Cutting Board</a>
                        <span class="quantity-price">1 x <span class="amount">$100.00</span></span>
                        <a href="#" class="remove">×</a>
                    </div>
                </li>
                <li>
                    <a href="product-details.html" class="image"><img src="<?php echo fullTemplateUri('assets/images/product/cart-product-2.webp'); ?>" alt="Cart product Image"></a>
                    <div class="content">
                        <a href="product-details.html" class="title">Lucky Wooden Elephant</a>
                        <span class="quantity-price">1 x <span class="amount">$35.00</span></span>
                        <a href="#" class="remove">×</a>
                    </div>
                </li>
                <li>
                    <a href="product-details.html" class="image"><img src="<?php echo fullTemplateUri('assets/images/product/cart-product-3.webp'); ?>" alt="Cart product Image"></a>
                    <div class="content">
                        <a href="product-details.html" class="title">Fish Cut Out Set</a>
                        <span class="quantity-price">1 x <span class="amount">$9.00</span></span>
                        <a href="#" class="remove">×</a>
                    </div>
                </li>
            </ul>
        </div>
        <div class="foot">
            <div class="buttons">
                <a href="<?php echo esc_url(graceartWishlistUrl()); ?>" class="btn btn-dark btn-hover-primary"><?php esc_html_e('Zobraziť wishlist', 'graceart'); ?></a>
            </div>
        </div>
    </div>
</div>
<!-- OffCanvas Wishlist End -->

<!-- OffCanvas Cart Start -->
<div id="offcanvas-cart" class="offcanvas offcanvas-cart">
    <div class="inner">
        <div class="head">
            <span class="title">Cart</span>
            <button class="offcanvas-close">×</button>
        </div>
        <div class="body customScroll">
            <ul class="minicart-product-list">
                <li>
                    <a href="product-details.html" class="image"><img src="<?php echo fullTemplateUri('assets/images/product/cart-product-1.webp'); ?>" alt="Cart product Image"></a>
                    <div class="content">
                        <a href="product-details.html" class="title">Walnut Cutting Board</a>
                        <span class="quantity-price">1 x <span class="amount">$100.00</span></span>
                        <a href="#" class="remove">×</a>
                    </div>
                </li>
                <li>
                    <a href="product-details.html" class="image"><img src="<?php echo fullTemplateUri('assets/images/product/cart-product-2.webp'); ?>" alt="Cart product Image"></a>
                    <div class="content">
                        <a href="product-details.html" class="title">Lucky Wooden Elephant</a>
                        <span class="quantity-price">1 x <span class="amount">$35.00</span></span>
                        <a href="#" class="remove">×</a>
                    </div>
                </li>
                <li>
                    <a href="product-details.html" class="image"><img src="<?php echo fullTemplateUri('assets/images/product/cart-product-3.webp'); ?>" alt="Cart product Image"></a>
                    <div class="content">
                        <a href="product-details.html" class="title">Fish Cut Out Set</a>
                        <span class="quantity-price">1 x <span class="amount">$9.00</span></span>
                        <a href="#" class="remove">×</a>
                    </div>
                </li>
            </ul>
        </div>
        <div class="foot">
            <div class="sub-total">
                <strong>Subtotal :</strong>
                <span class="amount">$144.00</span>
            </div>
            <div class="buttons">
                <a href="<?php echo esc_url(graceartCartUrl()); ?>" class="btn btn-dark btn-hover-primary"><?php esc_html_e('Zobraziť košík', 'graceart'); ?></a>
                <a href="<?php echo esc_url(graceartCheckoutUrl()); ?>" class="btn btn-outline-dark"><?php esc_html_e('Pokladňa', 'graceart'); ?></a>
            </div>
            <p class="minicart-message">Free Shipping on All Orders Over $100!</p>
        </div>
    </div>
</div>
<!-- OffCanvas Cart End -->

<!-- OffCanvas Search Start -->
<div id="offcanvas-mobile-menu" class="offcanvas offcanvas-mobile-menu">
    <div class="inner customScroll">
        <div class="offcanvas-menu-search-form">
            <form action="#">
                <input type="text" placeholder="Search...">
                <button><i class="fas fa-search"></i></button>
            </form>
        </div>
        <div class="offcanvas-menu">
            <?php graceartMobileMenu(); ?>
        </div>
        <div class="offcanvas-buttons">
            <div class="header-tools">
                <div class="header-login">
                    <a href="<?php echo esc_url(graceartMyAccountUrl()); ?>"><i class="far fa-user"></i></a>
                </div>
                <div class="header-wishlist">
                    <a href="<?php echo esc_url(graceartWishlistUrl()); ?>"><span class="wishlist-count"><?php echo esc_html((string) graceartWishlistCount()); ?></span><i class="far fa-heart"></i></a>
                </div>
                <div class="header-cart">
                    <a href="<?php echo esc_url(graceartCartUrl()); ?>"><span class="cart-count"><?php echo esc_html((string) graceartCartCount()); ?></span><i class="fas fa-shopping-cart"></i></a>
                </div>
            </div>
        </div>
        <div class="offcanvas-social">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
        </div>
    </div>
</div>
<!-- OffCanvas Search End -->

<div class="offcanvas-overlay"></div>
