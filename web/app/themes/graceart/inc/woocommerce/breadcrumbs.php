<?php

/**
 * Label for the leading "Domov" crumb, or '' to leave it out.
 *
 * WooCommerce only adds the home crumb when this is non-empty. Plain pages get
 * no home crumb, which leaves them with a single crumb (their own title) that
 * graceartHasBreadcrumbTrail() then suppresses — otherwise it would sit under
 * the <h1> looking like a duplicated subtitle. Shop archives and products keep
 * it, so they get a real trail such as "Domov / Zápisníky".
 */
function graceartBreadcrumbHomeLabel(): string
{
    if (function_exists('is_page') && is_page()) {
        return '';
    }

    return __('Domov', 'graceart');
}

function graceartWooBreadcrumb(): void
{
    woocommerce_breadcrumb([
        'delimiter' => '',
        'wrap_before' => '<ul class="breadcrumb">',
        'wrap_after' => '</ul>',
        'before' => '<li class="breadcrumb-item">',
        'after' => '</li>',
        'home' => graceartBreadcrumbHomeLabel(),
    ]);
}

function graceartLocalizeWooPageLabel(string $label): string
{
    $labels = [
        'Home' => __('Domov', 'graceart'),
        'Cart' => __('Košík', 'graceart'),
        'Checkout' => __('Zhrnutie objednávky', 'graceart'),
        'Shop' => __('Obchod', 'graceart'),
    ];

    return $labels[$label] ?? $label;
}

add_filter('woocommerce_get_breadcrumb', function (array $crumbs): array {
    return array_values(array_map(function (array $crumb): array {
        if (isset($crumb[0])) {
            $crumb[0] = graceartLocalizeWooPageLabel($crumb[0]);
        }

        return $crumb;
    }, $crumbs));
});

/**
 * The breadcrumb trail as WooCommerce would render it, after the theme's filter.
 */
function graceartBreadcrumbTrail(): array
{
    if (! class_exists('WC_Breadcrumb')) {
        return [];
    }

    $breadcrumb = new WC_Breadcrumb();
    $home = graceartBreadcrumbHomeLabel();

    // Mirror woocommerce_breadcrumb(), which adds the home crumb itself, so the
    // count here matches what would actually be rendered.
    if ($home !== '') {
        $breadcrumb->add_crumb($home, apply_filters('woocommerce_breadcrumb_home_url', home_url()));
    }

    return $breadcrumb->generate();
}

/**
 * Whether there is a trail worth showing. A single crumb is always just the
 * current page's own name, which the page title already displays.
 */
function graceartHasBreadcrumbTrail(): bool
{
    return count(graceartBreadcrumbTrail()) >= 2;
}

function graceartPageHero(string $title, bool $show_breadcrumb = true): void
{
    ?>
    <div class="page-title-section section"<?php echo graceartBgImageAttr(fullTemplateUri('assets/images/bg/shop-zapisniky.png')); ?>>
        <div class="container">
            <div class="row">
                <div class="col">
                    <div class="page-title">
                        <h1 class="title"><?php echo esc_html($title); ?></h1>
                        <?php if ($show_breadcrumb && function_exists('woocommerce_breadcrumb') && graceartHasBreadcrumbTrail()) : ?>
                            <?php graceartWooBreadcrumb(); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
