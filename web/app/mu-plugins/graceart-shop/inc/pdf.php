<?php

/**
 * "GraceArt" invoice template for PDF Invoices & Packing Slips.
 *
 * The template lives in this plugin (templates/pdf/GraceArt) instead of a
 * theme, so invoices look the same whichever theme is active. Registered under
 * the plugin's "theme" source, which keeps the already saved template setting
 * ("theme/GraceArt") valid.
 */
add_filter('wpo_wcpdf_template_paths', function (array $paths): array {
    $paths['theme'] = GRACEART_SHOP_DIR . 'templates/pdf/';

    return $paths;
});
