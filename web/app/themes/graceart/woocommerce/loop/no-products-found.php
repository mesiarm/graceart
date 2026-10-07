<?php

defined('ABSPATH') || exit;

$message = is_product_category()
    ? __('V tejto kategórii sa zatiaľ nič nenašlo.', 'graceart')
    : __('Nenašli sa žiadne produkty zodpovedajúce vášmu výberu.', 'graceart');

wc_print_notice(esc_html($message), 'notice');
