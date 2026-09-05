<?php

function fullTemplatePath(string $path = ''): string
{
    return get_template_directory() . ($path ? '/' . ltrim($path, '/') : '');
}

function fullTemplateUri(string $path = ''): string
{
    return get_template_directory_uri() . ($path ? '/' . ltrim($path, '/') : '');
}

function graceartAssetVersion(string $path): string
{
    $full_path = fullTemplatePath($path);

    return file_exists($full_path) ? (string) filemtime($full_path) : wp_get_theme()->get('Version');
}

function graceartStyle(string $handle, string $path, array $deps = []): void
{
    wp_enqueue_style($handle, fullTemplateUri($path), $deps, graceartAssetVersion($path));
}

function graceartScript(string $handle, string $path, array $deps = []): void
{
    wp_enqueue_script($handle, fullTemplateUri($path), $deps, graceartAssetVersion($path), true);
}

/**
 * The gallery carousels, zoom and lightbox only exist on a single product.
 */
function graceartIsProductPage(): bool
{
    return function_exists('is_product') && is_product();
}

/**
 * Shop, product categories and tags, and search results all render the
 * isotope grid from woocommerce/archive-product.php.
 */
function graceartIsCatalogPage(): bool
{
    if (! function_exists('is_shop')) {
        return false;
    }

    return is_shop() || is_product_taxonomy() || is_search();
}

add_action('wp_enqueue_scripts', function () {
    $is_front = is_front_page();
    $is_product = graceartIsProductPage();
    $is_catalog = graceartIsCatalogPage();

    // Everywhere: header, offcanvas menu, footer.
    graceartStyle('bootstrap-style', 'assets/css/vendor/bootstrap.min.css');
    graceartStyle('fontawesome-style', 'assets/css/vendor/fontawesome.min.css');
    graceartStyle('themify-icons-style', 'assets/css/vendor/themify-icons.css');
    graceartStyle('custom-fonts-style', 'assets/css/vendor/customFonts.css');
    graceartStyle('select2-style', 'assets/css/plugins/select2.min.css');
    graceartStyle('perfect-scrollbar-style', 'assets/css/plugins/perfect-scrollbar.css');

    if ($is_front || $is_product) {
        graceartStyle('slick-style', 'assets/css/plugins/slick.css');
    }

    if ($is_front) {
        graceartStyle('swiper-style', 'assets/css/plugins/swiper.min.css');
    }

    if ($is_product) {
        graceartStyle('photoswipe-style', 'assets/css/plugins/photoswipe.css');
        graceartStyle('photoswipe-skin-style', 'assets/css/plugins/photoswipe-default-skin.css');
    }

    if ($is_catalog) {
        graceartStyle('nice-select-style', 'assets/css/plugins/nice-select.css');
    }

    graceartStyle('main-style', 'assets/css/style.min.css');
    graceartStyle('custom-style', 'assets/css/custom_styles.css', ['main-style']);

    graceartScript('modernizr-script', 'assets/js/vendor/modernizr-3.6.0.min.js');

    // WooCommerce already loads core jQuery, so the theme copy was a second one on every page.
    wp_enqueue_script('jquery');

    graceartScript('bootstrap-script', 'assets/js/vendor/bootstrap.bundle.min.js');
    graceartScript('select2-script', 'assets/js/plugins/select2.min.js', ['jquery']);
    graceartScript('perfect-scrollbar-script', 'assets/js/plugins/perfect-scrollbar.min.js');
    graceartScript('scrollup-script', 'assets/js/plugins/jquery.scrollUp.min.js', ['jquery']);

    if ($is_front) {
        graceartScript('swiper-script', 'assets/js/plugins/swiper.min.js');
    }

    if ($is_front || $is_product) {
        graceartScript('slick-script', 'assets/js/plugins/slick.min.js', ['jquery']);
    }

    if ($is_product) {
        graceartScript('photoswipe-script', 'assets/js/plugins/photoswipe.min.js');
        graceartScript('photoswipe-ui-script', 'assets/js/plugins/photoswipe-ui-default.min.js');
        graceartScript('zoom-script', 'assets/js/plugins/jquery.zoom.min.js', ['jquery']);
    }

    if ($is_catalog) {
        graceartScript('nice-select-script', 'assets/js/plugins/jquery.nice-select.min.js', ['jquery']);
        graceartScript('imagesloaded-script', 'assets/js/plugins/imagesloaded.pkgd.min.js');
        graceartScript('isotope-script', 'assets/js/plugins/isotope.pkgd.min.js');
        graceartScript('match-height-script', 'assets/js/plugins/jquery.matchHeight-min.js', ['jquery']);
    }

    graceartScript('main-script', 'assets/js/main.js', ['jquery', 'wc-add-to-cart-variation']);
});

/**
 * The YITH wishlist widget ships a React bundle (react, react-dom, lodash,
 * moment and the lapilli-ui components) on every page. The catalogue buttons
 * add to the wishlist through a plain URL, so the bundle is only needed on the
 * wishlist page itself, where the table has its own interactions.
 */
add_action('wp_enqueue_scripts', function (): void {
    if (is_admin()) {
        return;
    }

    if (function_exists('yith_wcwl_is_wishlist_page') && yith_wcwl_is_wishlist_page()) {
        return;
    }

    foreach ([
        'yith-wcwl-add-to-wishlist',
        'lapilli-ui-components',
        'lapilli-ui-date',
        'lapilli-ui-styles',
    ] as $handle) {
        wp_dequeue_script($handle);
        wp_deregister_script($handle);
    }
}, 99);

/**
 * Backgrounds were applied from data-bg-image by main.js, which runs from the
 * footer. The preload scanner never saw those URLs, so the LCP image only
 * started downloading after jQuery and main.js had loaded and executed.
 * Render the style inline instead.
 */
function graceartBgImageAttr(string $url): string
{
    if ($url === '') {
        return '';
    }

    return sprintf(" style=\"background-image:url('%s')\"", esc_url($url));
}

/**
 * Tell the browser about the first hero slide as early as possible.
 */
add_action('wp_head', function (): void {
    if (! is_front_page() || ! function_exists('graceartHomepageHeroSlides')) {
        return;
    }

    $slides = graceartHomepageHeroSlides((int) get_queried_object_id());
    $first = $slides[0]['image'] ?? '';

    if ($first) {
        printf('<link rel="preload" as="image" href="%s" fetchpriority="high">' . "\n", esc_url($first));
    }
}, 2);

/**
 * Stylesheets that are not needed for the first paint. Bootstrap, the theme
 * stylesheet and the slider CSS stay render-blocking because the header and
 * the hero are above the fold.
 *
 * @return array<int, string>
 */
function graceartDeferredStyles(): array
{
    return array_merge([
        'fontawesome-style',
        'themify-icons-style',
        'select2-style',
        'perfect-scrollbar-style',
        'nice-select-style',
        'photoswipe-style',
        'photoswipe-skin-style',
    ], graceartCriticalDeferredStyles());
}

add_filter('style_loader_tag', function (string $tag, string $handle, string $href, string $media): string {
    if (is_admin() || ! in_array($handle, graceartDeferredStyles(), true)) {
        return $tag;
    }

    return sprintf(
        '<link rel="preload" as="style" id="%1$s-css" href="%2$s" media="%3$s" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n"
            . '<noscript><link rel="stylesheet" href="%2$s" media="%3$s"></noscript>' . "\n",
        esc_attr($handle),
        esc_url($href),
        esc_attr($media),
    );
}, 10, 4);

/**
 * Path to the extracted above-the-fold CSS, if it has been generated.
 */
function graceartCriticalCssPath(): string
{
    $file = match (true) {
        is_front_page() => 'assets/css/critical-front.css',
        graceartIsProductPage() => 'assets/css/critical-product.css',
        graceartIsCatalogPage() => 'assets/css/critical-catalog.css',
        default => '',
    };

    return $file === '' ? '' : fullTemplatePath($file);
}

function graceartHasCriticalCss(): bool
{
    $path = graceartCriticalCssPath();

    return $path !== "" && is_readable($path);
}

/**
 * With the above-the-fold rules inlined, the full stylesheets no longer need
 * to block the first paint. Without the file these stay render-blocking, so
 * the site is never left unstyled.
 *
 * @return array<int, string>
 */
function graceartCriticalDeferredStyles(): array
{
    return graceartHasCriticalCss()
        ? ['bootstrap-style', 'main-style', 'custom-style']
        : [];
}

add_action('wp_head', function (): void {
    if (is_admin() || ! graceartHasCriticalCss()) {
        return;
    }

    $css = file_get_contents(graceartCriticalCssPath());

    if ($css !== false && $css !== '') {
        printf('<style id="graceart-critical">%s</style>' . "\n", $css);
    }
}, 1);
