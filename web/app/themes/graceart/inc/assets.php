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

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('bootstrap-style', fullTemplateUri('assets/css/vendor/bootstrap.min.css'), [], graceartAssetVersion('assets/css/vendor/bootstrap.min.css'));
    wp_enqueue_style('fontawesome-style', fullTemplateUri('assets/css/vendor/fontawesome.min.css'), [], graceartAssetVersion('assets/css/vendor/fontawesome.min.css'));
    wp_enqueue_style('themify-icons-style', fullTemplateUri('assets/css/vendor/themify-icons.css'), [], graceartAssetVersion('assets/css/vendor/themify-icons.css'));
    wp_enqueue_style('custom-fonts-style', fullTemplateUri('assets/css/vendor/customFonts.css'), [], graceartAssetVersion('assets/css/vendor/customFonts.css'));

    wp_enqueue_style('select2-style', fullTemplateUri('assets/css/plugins/select2.min.css'), [], graceartAssetVersion('assets/css/plugins/select2.min.css'));
    wp_enqueue_style('perfect-scrollbar-style', fullTemplateUri('assets/css/plugins/perfect-scrollbar.css'), [], graceartAssetVersion('assets/css/plugins/perfect-scrollbar.css'));
    wp_enqueue_style('swiper-style', fullTemplateUri('assets/css/plugins/swiper.min.css'), [], graceartAssetVersion('assets/css/plugins/swiper.min.css'));
    wp_enqueue_style('nice-select-style', fullTemplateUri('assets/css/plugins/nice-select.css'), [], graceartAssetVersion('assets/css/plugins/nice-select.css'));
    wp_enqueue_style('ion-range-slider-style', fullTemplateUri('assets/css/plugins/ion.rangeSlider.min.css'), [], graceartAssetVersion('assets/css/plugins/ion.rangeSlider.min.css'));
    wp_enqueue_style('photoswipe-style', fullTemplateUri('assets/css/plugins/photoswipe.css'), [], graceartAssetVersion('assets/css/plugins/photoswipe.css'));
    wp_enqueue_style('photoswipe-skin-style', fullTemplateUri('assets/css/plugins/photoswipe-default-skin.css'), [], graceartAssetVersion('assets/css/plugins/photoswipe-default-skin.css'));
    wp_enqueue_style('magnific-popup-style', fullTemplateUri('assets/css/plugins/magnific-popup.css'), [], graceartAssetVersion('assets/css/plugins/magnific-popup.css'));
    wp_enqueue_style('slick-style', fullTemplateUri('assets/css/plugins/slick.css'), [], graceartAssetVersion('assets/css/plugins/slick.css'));

    wp_enqueue_style('main-style', fullTemplateUri('assets/css/style.min.css'), [], graceartAssetVersion('assets/css/style.min.css'));

    wp_enqueue_script('modernizr-script', fullTemplateUri('assets/js/vendor/modernizr-3.6.0.min.js'), [], graceartAssetVersion('assets/js/vendor/modernizr-3.6.0.min.js'), true);
    wp_enqueue_script('jquery-script', fullTemplateUri('assets/js/vendor/jquery-3.4.1.min.js'), [], graceartAssetVersion('assets/js/vendor/jquery-3.4.1.min.js'), true);
    wp_enqueue_script('jquery-migrate-script', fullTemplateUri('assets/js/vendor/jquery-migrate-3.1.0.min.js'), [], graceartAssetVersion('assets/js/vendor/jquery-migrate-3.1.0.min.js'), true);
    wp_enqueue_script('bootstrap-script', fullTemplateUri('assets/js/vendor/bootstrap.bundle.min.js'), [], graceartAssetVersion('assets/js/vendor/bootstrap.bundle.min.js'), true);

    wp_enqueue_script('select2-script', fullTemplateUri('assets/js/plugins/select2.min.js'), [], graceartAssetVersion('assets/js/plugins/select2.min.js'), true);
    wp_enqueue_script('nice-select-script', fullTemplateUri('assets/js/plugins/jquery.nice-select.min.js'), [], graceartAssetVersion('assets/js/plugins/jquery.nice-select.min.js'), true);
    wp_enqueue_script('perfect-scrollbar-script', fullTemplateUri('assets/js/plugins/perfect-scrollbar.min.js'), [], graceartAssetVersion('assets/js/plugins/perfect-scrollbar.min.js'), true);
    wp_enqueue_script('swiper-script', fullTemplateUri('assets/js/plugins/swiper.min.js'), [], graceartAssetVersion('assets/js/plugins/swiper.min.js'), true);
    wp_enqueue_script('slick-script', fullTemplateUri('assets/js/plugins/slick.min.js'), [], graceartAssetVersion('assets/js/plugins/slick.min.js'), true);
    wp_enqueue_script('mo-script', fullTemplateUri('assets/js/plugins/mo.min.js'), [], graceartAssetVersion('assets/js/plugins/mo.min.js'), true);
    wp_enqueue_script('ajaxchimp-script', fullTemplateUri('assets/js/plugins/jquery.ajaxchimp.min.js'), [], graceartAssetVersion('assets/js/plugins/jquery.ajaxchimp.min.js'), true);
    wp_enqueue_script('countdown-script', fullTemplateUri('assets/js/plugins/jquery.countdown.min.js'), [], graceartAssetVersion('assets/js/plugins/jquery.countdown.min.js'), true);
    wp_enqueue_script('imagesloaded-script', fullTemplateUri('assets/js/plugins/imagesloaded.pkgd.min.js'), [], graceartAssetVersion('assets/js/plugins/imagesloaded.pkgd.min.js'), true);
    wp_enqueue_script('isotope-script', fullTemplateUri('assets/js/plugins/isotope.pkgd.min.js'), [], graceartAssetVersion('assets/js/plugins/isotope.pkgd.min.js'), true);
    wp_enqueue_script('match-height-script', fullTemplateUri('assets/js/plugins/jquery.matchHeight-min.js'), [], graceartAssetVersion('assets/js/plugins/jquery.matchHeight-min.js'), true);
    wp_enqueue_script('ion-range-slider-script', fullTemplateUri('assets/js/plugins/ion.rangeSlider.min.js'), [], graceartAssetVersion('assets/js/plugins/ion.rangeSlider.min.js'), true);
    wp_enqueue_script('photoswipe-script', fullTemplateUri('assets/js/plugins/photoswipe.min.js'), [], graceartAssetVersion('assets/js/plugins/photoswipe.min.js'), true);
    wp_enqueue_script('photoswipe-ui-script', fullTemplateUri('assets/js/plugins/photoswipe-ui-default.min.js'), [], graceartAssetVersion('assets/js/plugins/photoswipe-ui-default.min.js'), true);
    wp_enqueue_script('zoom-script', fullTemplateUri('assets/js/plugins/jquery.zoom.min.js'), [], graceartAssetVersion('assets/js/plugins/jquery.zoom.min.js'), true);
    wp_enqueue_script('resize-sensor-script', fullTemplateUri('assets/js/plugins/ResizeSensor.js'), [], graceartAssetVersion('assets/js/plugins/ResizeSensor.js'), true);
    wp_enqueue_script('sticky-sidebar-script', fullTemplateUri('assets/js/plugins/jquery.sticky-sidebar.min.js'), [], graceartAssetVersion('assets/js/plugins/jquery.sticky-sidebar.min.js'), true);
    wp_enqueue_script('product360-script', fullTemplateUri('assets/js/plugins/product360.js'), [], graceartAssetVersion('assets/js/plugins/product360.js'), true);
    wp_enqueue_script('magnific-popup-script', fullTemplateUri('assets/js/plugins/jquery.magnific-popup.min.js'), [], graceartAssetVersion('assets/js/plugins/jquery.magnific-popup.min.js'), true);
    wp_enqueue_script('scrollup-script', fullTemplateUri('assets/js/plugins/jquery.scrollUp.min.js'), [], graceartAssetVersion('assets/js/plugins/jquery.scrollUp.min.js'), true);
    wp_enqueue_script('scrollax-script', fullTemplateUri('assets/js/plugins/scrollax.min.js'), [], graceartAssetVersion('assets/js/plugins/scrollax.min.js'), true);

    wp_enqueue_script('main-script', fullTemplateUri('assets/js/main.js'), [], graceartAssetVersion('assets/js/main.js'), true);
});
