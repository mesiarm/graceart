<!DOCTYPE html>
<html class="no-js" <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Favicon: SVG where supported, PNG elsewhere, touch icon and manifest for home screens -->
    <link rel="icon" href="<?php echo fullTemplateUri('assets/images/favicon/favicon.svg'); ?>" type="image/svg+xml">
    <link rel="icon" href="<?php echo fullTemplateUri('assets/images/favicon/favicon-32.png'); ?>" sizes="32x32" type="image/png">
    <link rel="apple-touch-icon" href="<?php echo fullTemplateUri('assets/images/favicon/apple-touch-icon.png'); ?>">
    <link rel="manifest" href="<?php echo fullTemplateUri('assets/images/favicon/site.webmanifest'); ?>">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<a class="graceart-skip-link graceart-visually-hidden" href="#content"><?php esc_html_e('Preskočiť na obsah', 'graceart'); ?></a>
<!-- Header Section Start -->
<div class="header-section section bg-white d-none d-xl-block">
    <div class="container">
        <div class="row row-cols-lg-3 align-items-center">
            <div class="col"></div>
            <div class="col">
                <div class="header-logo justify-content-center">
                    <a href="<?php echo home_url(); ?>">
                        <img src="<?php echo fullTemplateUri('assets/images/logo/logo.jpg'); ?>" width="463" height="100" alt="Grace Art Logo">
                    </a>
                </div>
            </div>
            <!-- Header Logo End -->

            <!-- Header Tools Start -->
            <div class="col">
                <div class="header-tools justify-content-end">
                    <div class="header-search">
                        <a href="#offcanvas-search" class="offcanvas-toggle" aria-label="<?php esc_attr_e('Hľadať', 'graceart'); ?>"><i class="fas fa-search" aria-hidden="true"></i></a>
                    </div>
                    <div class="header-wishlist">
                        <a href="<?php echo esc_url(graceartWishlistUrl()); ?>" aria-label="<?php esc_attr_e('Zoznam prianí', 'graceart'); ?>"><span class="wishlist-count" aria-hidden="true"><?php echo esc_html((string) graceartWishlistCount()); ?></span><i class="far fa-heart" aria-hidden="true"></i></a>
                    </div>
                    <div class="header-cart">
                        <a href="<?php echo esc_url(graceartCartUrl()); ?>" aria-label="<?php esc_attr_e('Košík', 'graceart'); ?>"><span class="cart-count" aria-hidden="true"><?php echo esc_html((string) graceartCartCount()); ?></span><i class="fas fa-shopping-cart" aria-hidden="true"></i></a>
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
                        <img src="<?php echo fullTemplateUri('assets/images/logo/logo.jpg'); ?>" width="463" height="100" alt="Grace Art Logo">
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
                    <div class="header-search d-none d-sm-block">
                        <a href="#offcanvas-search" class="offcanvas-toggle" aria-label="<?php esc_attr_e('Hľadať', 'graceart'); ?>"><i class="fas fa-search" aria-hidden="true"></i></a>
                    </div>
                    <div class="header-wishlist">
                        <a href="<?php echo esc_url(graceartWishlistUrl()); ?>" aria-label="<?php esc_attr_e('Zoznam prianí', 'graceart'); ?>"><span class="wishlist-count" aria-hidden="true"><?php echo esc_html((string) graceartWishlistCount()); ?></span><i class="far fa-heart" aria-hidden="true"></i></a>
                    </div>
                    <div class="header-cart">
                        <a href="<?php echo esc_url(graceartCartUrl()); ?>" aria-label="<?php esc_attr_e('Košík', 'graceart'); ?>"><span class="cart-count" aria-hidden="true"><?php echo esc_html((string) graceartCartCount()); ?></span><i class="fas fa-shopping-cart" aria-hidden="true"></i></a>
                    </div>
                    <div class="mobile-menu-toggle d-xl-none">
                        <a href="#offcanvas-mobile-menu" class="offcanvas-toggle" aria-label="<?php esc_attr_e('Menu', 'graceart'); ?>">
                            <svg viewBox="0 0 800 600" aria-hidden="true" focusable="false">
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
                        <img src="<?php echo fullTemplateUri('assets/images/logo/logo.jpg'); ?>" width="463" height="100" alt="Grace Art Logo">
                    </a>
                </div>
            </div>
            <!-- Header Logo End -->

            <!-- Header Tools Start -->
            <div class="col-auto">
                <div class="header-tools justify-content-end">
                    <div class="header-search d-none d-sm-block">
                        <a href="#offcanvas-search" class="offcanvas-toggle" aria-label="<?php esc_attr_e('Hľadať', 'graceart'); ?>"><i class="fas fa-search" aria-hidden="true"></i></a>
                    </div>
                    <div class="header-wishlist d-none d-sm-block">
                        <a href="<?php echo esc_url(graceartWishlistUrl()); ?>" aria-label="<?php esc_attr_e('Zoznam prianí', 'graceart'); ?>"><span class="wishlist-count" aria-hidden="true"><?php echo esc_html((string) graceartWishlistCount()); ?></span><i class="far fa-heart" aria-hidden="true"></i></a>
                    </div>
                    <div class="header-cart">
                        <a href="<?php echo esc_url(graceartCartUrl()); ?>" aria-label="<?php esc_attr_e('Košík', 'graceart'); ?>"><span class="cart-count" aria-hidden="true"><?php echo esc_html((string) graceartCartCount()); ?></span><i class="fas fa-shopping-cart" aria-hidden="true"></i></a>
                    </div>
                    <div class="mobile-menu-toggle">
                        <a href="#offcanvas-mobile-menu" class="offcanvas-toggle" aria-label="<?php esc_attr_e('Menu', 'graceart'); ?>">
                            <svg viewBox="0 0 800 600" aria-hidden="true" focusable="false">
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
                        <img src="<?php echo fullTemplateUri('assets/images/logo/logo.jpg'); ?>" width="463" height="100" alt="Grace Art Logo">
                    </a>
                </div>
            </div>
            <!-- Header Logo End -->

            <!-- Header Tools Start -->
            <div class="col-auto">
                <div class="header-tools justify-content-end">
                    <div class="header-search d-none d-sm-block">
                        <a href="#offcanvas-search" class="offcanvas-toggle" aria-label="<?php esc_attr_e('Hľadať', 'graceart'); ?>"><i class="fas fa-search" aria-hidden="true"></i></a>
                    </div>
                    <div class="header-wishlist d-none d-sm-block">
                        <a href="<?php echo esc_url(graceartWishlistUrl()); ?>" aria-label="<?php esc_attr_e('Zoznam prianí', 'graceart'); ?>"><span class="wishlist-count" aria-hidden="true"><?php echo esc_html((string) graceartWishlistCount()); ?></span><i class="far fa-heart" aria-hidden="true"></i></a>
                    </div>
                    <div class="header-cart">
                        <a href="<?php echo esc_url(graceartCartUrl()); ?>" aria-label="<?php esc_attr_e('Košík', 'graceart'); ?>"><span class="cart-count" aria-hidden="true"><?php echo esc_html((string) graceartCartCount()); ?></span><i class="fas fa-shopping-cart" aria-hidden="true"></i></a>
                    </div>
                    <div class="mobile-menu-toggle">
                        <a href="#offcanvas-mobile-menu" class="offcanvas-toggle" aria-label="<?php esc_attr_e('Menu', 'graceart'); ?>">
                            <svg viewBox="0 0 800 600" aria-hidden="true" focusable="false">
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
            <button class="offcanvas-close" aria-label="<?php esc_attr_e('Zavrieť', 'graceart'); ?>">×</button>
            <form action="<?php echo esc_url(home_url('/')); ?>" method="get">
                <input type="search" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="<?php esc_attr_e('Hľadať produkty...', 'graceart'); ?>" aria-label="<?php esc_attr_e('Hľadať produkty', 'graceart'); ?>">
            </form>
        </div>

    </div>
</div>
<!-- OffCanvas Search End -->


<!-- OffCanvas Mobile Menu Start -->
<div id="offcanvas-mobile-menu" class="offcanvas offcanvas-mobile-menu">
    <div class="inner customScroll">
        <div class="offcanvas-menu-search-form">
            <form action="<?php echo esc_url(home_url('/')); ?>" method="get">
                <input type="text" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="<?php esc_attr_e('Hľadať...', 'graceart'); ?>">
                <button type="submit" aria-label="<?php esc_attr_e('Hľadať', 'graceart'); ?>"><i class="fas fa-search" aria-hidden="true"></i></button>
            </form>
        </div>
        <div class="offcanvas-menu">
            <?php graceartMobileMenu(); ?>
        </div>
        <div class="offcanvas-buttons">
            <div class="header-tools">
                <div class="header-wishlist">
                    <a href="<?php echo esc_url(graceartWishlistUrl()); ?>" aria-label="<?php esc_attr_e('Zoznam prianí', 'graceart'); ?>"><span class="wishlist-count" aria-hidden="true"><?php echo esc_html((string) graceartWishlistCount()); ?></span><i class="far fa-heart" aria-hidden="true"></i></a>
                </div>
                <div class="header-cart">
                    <a href="<?php echo esc_url(graceartCartUrl()); ?>" aria-label="<?php esc_attr_e('Košík', 'graceart'); ?>"><span class="cart-count" aria-hidden="true"><?php echo esc_html((string) graceartCartCount()); ?></span><i class="fas fa-shopping-cart" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>
        <div class="offcanvas-social">
            <a href="<?php echo esc_url(graceartFacebookUrl()); ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="<?php echo esc_url(graceartInstagramUrl()); ?>" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
        </div>
    </div>
</div>
<!-- OffCanvas Search End -->

<div class="offcanvas-overlay"></div>

<main id="content" class="site-content" tabindex="-1">
