<?php

function graceartWooBreadcrumb(): void
{
    woocommerce_breadcrumb([
        'delimiter' => '',
        'wrap_before' => '<ul class="breadcrumb">',
        'wrap_after' => '</ul>',
        'before' => '<li class="breadcrumb-item">',
        'after' => '</li>',
        'home' => false,
    ]);
}

add_filter('woocommerce_get_breadcrumb', function (array $crumbs): array {
    $crumbs = array_filter($crumbs, function (array $crumb): bool {
        return ($crumb[0] ?? '') !== _x('Home', 'breadcrumb', 'woocommerce');
    });

    return array_values(array_map(function (array $crumb): array {
        if (($crumb[0] ?? '') === 'Shop') {
            $crumb[0] = __('Obchod', 'graceart');
        }

        return $crumb;
    }, $crumbs));
});

function graceartPageHero(string $title): void
{
    ?>
    <div class="page-title-section section" data-bg-image="<?php echo esc_url(fullTemplateUri('assets/images/bg/shop-zapisniky.png')); ?>">
        <div class="container">
            <div class="row">
                <div class="col">
                    <div class="page-title">
                        <h1 class="title"><?php echo esc_html($title); ?></h1>
                        <?php if (function_exists('woocommerce_breadcrumb')) : ?>
                            <?php graceartWooBreadcrumb(); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
