<?php

$graceart_woocommerce_files = [
    'setup.php',
    'company.php',
    'urls.php',
    'shipping.php',
    'cart.php',
    'notices.php',
    'breadcrumbs.php',
    'product.php',
    'catalog.php',
    'checkout.php',
    'search.php',
    'wishlist.php',
];

foreach ($graceart_woocommerce_files as $graceart_woocommerce_file) {
    require_once __DIR__ . '/woocommerce/' . $graceart_woocommerce_file;
}
