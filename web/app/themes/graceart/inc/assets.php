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
 * The gallery carousels and lightbox only exist on a single product.
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

    // WooCommerce already loads core jQuery, so the theme copy was a second one on every page.
    wp_enqueue_script('jquery');

    // Not loaded any more: bootstrap.bundle.js (nothing uses data-bs-* or its
    // API; the grid CSS stays), select2 (its only select was the removed search
    // category filter), perfect-scrollbar (the mobile menu scrolls natively),
    // modernizr (no feature classes are consumed).
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
    }

    if ($is_catalog) {
        graceartScript('nice-select-script', 'assets/js/plugins/jquery.nice-select.min.js', ['jquery']);
        graceartScript('imagesloaded-script', 'assets/js/plugins/imagesloaded.pkgd.min.js');
        graceartScript('isotope-script', 'assets/js/plugins/isotope.pkgd.min.js');
        graceartScript('match-height-script', 'assets/js/plugins/jquery.matchHeight-min.js', ['jquery']);
    }

    // The variation form (and its wp-util/underscore chain) only exists on a
    // product page; main.js listens to it through a delegated handler.
    graceartScript('main-script', 'assets/js/main.js', $is_product ? ['jquery', 'wc-add-to-cart-variation'] : ['jquery']);
});

/**
 * Small site-wide loads with no job here: jQuery Migrate only logs deprecation
 * notices, the emoji scripts replace characters every browser renders natively,
 * and prettyPhoto is YITH's popup skin, only used by its script on the
 * wishlist page.
 */
add_action('wp_default_scripts', function (WP_Scripts $scripts): void {
    if (is_admin() || empty($scripts->registered['jquery'])) {
        return;
    }

    $scripts->registered['jquery']->deps = array_diff($scripts->registered['jquery']->deps, ['jquery-migrate']);
});

remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

add_action('wp_enqueue_scripts', function (): void {
    if (! (function_exists('yith_wcwl_is_wishlist_page') && yith_wcwl_is_wishlist_page())) {
        wp_dequeue_style('woocommerce_prettyPhoto_css');
    }
}, 99);

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
 * Jost is on every line of text; fetch it before the CSS asks for it. Slovak
 * needs the latin-ext subset as well as latin.
 */
add_action('wp_head', function (): void {
    foreach (['jost-latin.woff2', 'jost-latin-ext.woff2'] as $file) {
        printf(
            '<link rel="preload" as="font" type="font/woff2" href="%s" crossorigin>' . "\n",
            esc_url(fullTemplateUri('assets/fonts/jost/' . $file))
        );
    }
}, 2);

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
