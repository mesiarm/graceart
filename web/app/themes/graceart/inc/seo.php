<?php

/**
 * Meta description, canonical-friendly Open Graph and Twitter tags.
 *
 * There is no SEO plugin on this site, so without this the pages ship with no
 * description and nothing for social previews to read.
 */

/**
 * Trim text to a length search engines will actually display.
 */
function graceartSeoTrim(string $text, int $length = 155): string
{
    $text = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags(strip_shortcodes($text))));

    if ($text === '') {
        return '';
    }

    if (function_exists('mb_strlen') && mb_strlen($text) <= $length) {
        return $text;
    }

    $text = function_exists('mb_substr') ? mb_substr($text, 0, $length) : substr($text, 0, $length);
    $cut = function_exists('mb_strrpos') ? mb_strrpos($text, ' ') : strrpos($text, ' ');

    if ($cut !== false && $cut > 40) {
        $text = function_exists('mb_substr') ? mb_substr($text, 0, $cut) : substr($text, 0, $cut);
    }

    return rtrim($text, " ,.;:-") . '…';
}

/**
 * The best available description for whatever is currently being viewed.
 */
function graceartSeoDescription(): string
{
    $description = '';

    if (is_front_page()) {
        $description = (string) get_option('blogdescription');
    } elseif (function_exists('is_product') && is_product()) {
        $product = wc_get_product(get_queried_object_id());

        if ($product instanceof WC_Product) {
            $description = $product->get_short_description() ?: $product->get_description();
        }
    } elseif (function_exists('is_product_category') && (is_product_category() || is_product_tag())) {
        $term = get_queried_object();
        $description = $term instanceof WP_Term ? $term->description : '';
    } elseif (function_exists('is_shop') && is_shop()) {
        $shop = get_post(wc_get_page_id('shop'));
        $description = $shop instanceof WP_Post ? ($shop->post_excerpt ?: $shop->post_content) : '';
    } elseif (is_singular()) {
        $post = get_queried_object();

        if ($post instanceof WP_Post) {
            $description = $post->post_excerpt ?: $post->post_content;
        }
    }

    if (trim(wp_strip_all_tags((string) $description)) === '') {
        $description = (string) get_option('blogdescription');
    }

    return graceartSeoTrim((string) apply_filters('graceart_seo_description', $description));
}

/**
 * Image representing the current view, for social previews.
 */
function graceartSeoImage(): string
{
    $image_id = 0;

    if (function_exists('is_product') && is_product()) {
        $product = wc_get_product(get_queried_object_id());
        $image_id = $product instanceof WC_Product ? (int) $product->get_image_id() : 0;
    } elseif (is_singular()) {
        $image_id = (int) get_post_thumbnail_id(get_queried_object_id());
    } elseif (function_exists('is_product_category') && is_product_category()) {
        $term = get_queried_object();
        $image_id = $term instanceof WP_Term ? (int) get_term_meta($term->term_id, 'thumbnail_id', true) : 0;
    }

    if ($image_id) {
        $url = wp_get_attachment_image_url($image_id, 'large');

        if ($url) {
            return $url;
        }
    }

    return fullTemplateUri('assets/images/logo/logo.jpg');
}

/**
 * Canonical URL for the current view.
 */
function graceartSeoUrl(): string
{
    if (is_front_page()) {
        return home_url('/');
    }

    if (is_singular()) {
        return (string) get_permalink(get_queried_object_id());
    }

    if (is_tax() || is_category() || is_tag()) {
        $link = get_term_link(get_queried_object());

        if (! is_wp_error($link)) {
            return (string) $link;
        }
    }

    if (function_exists('is_shop') && is_shop()) {
        return (string) get_permalink(wc_get_page_id('shop'));
    }

    return home_url(add_query_arg([], $GLOBALS['wp']->request ?? ''));
}

add_action('wp_head', function (): void {
    if (is_404() || is_search()) {
        return;
    }

    $description = graceartSeoDescription();
    $title = wp_get_document_title();
    $url = graceartSeoUrl();
    $image = graceartSeoImage();
    $type = (function_exists('is_product') && is_product()) ? 'product' : (is_singular() ? 'article' : 'website');

    if ($description !== '') {
        printf('<meta name="description" content="%s">' . "\n", esc_attr($description));
    }

    printf('<meta property="og:type" content="%s">' . "\n", esc_attr($type));
    printf('<meta property="og:site_name" content="%s">' . "\n", esc_attr(get_bloginfo('name')));
    printf('<meta property="og:locale" content="%s">' . "\n", esc_attr(get_locale()));
    printf('<meta property="og:title" content="%s">' . "\n", esc_attr($title));
    printf('<meta property="og:url" content="%s">' . "\n", esc_url($url));

    if ($description !== '') {
        printf('<meta property="og:description" content="%s">' . "\n", esc_attr($description));
    }

    if ($image !== '') {
        printf('<meta property="og:image" content="%s">' . "\n", esc_url($image));
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    } else {
        echo '<meta name="twitter:card" content="summary">' . "\n";
    }

    printf('<meta name="twitter:title" content="%s">' . "\n", esc_attr($title));

    if ($description !== '') {
        printf('<meta name="twitter:description" content="%s">' . "\n", esc_attr($description));
    }

    // Price/availability are useful on product shares; the full Product schema
    // is already emitted by WooCommerce as JSON-LD.
    if (function_exists('is_product') && is_product()) {
        $product = wc_get_product(get_queried_object_id());

        if ($product instanceof WC_Product) {
            printf('<meta property="product:price:amount" content="%s">' . "\n", esc_attr((string) wc_get_price_to_display($product)));
            printf('<meta property="product:price:currency" content="%s">' . "\n", esc_attr(get_woocommerce_currency()));
            printf('<meta property="product:availability" content="%s">' . "\n", esc_attr($product->is_in_stock() ? 'in stock' : 'out of stock'));
        }
    }
}, 5);
