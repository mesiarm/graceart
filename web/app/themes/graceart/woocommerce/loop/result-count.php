<?php

defined('ABSPATH') || exit;

if ($total <= 0) {
    return;
}
?>

<p class="woocommerce-result-count">
    <?php
    if ($total === 1) {
        esc_html_e('Zobrazuje sa jediný produkt', 'graceart');
    } elseif ($total <= $per_page || -1 === $per_page) {
        printf(esc_html__('Zobrazuje sa všetkých %d produktov', 'graceart'), esc_html((string) $total));
    } else {
        $first = ($per_page * $current) - $per_page + 1;
        $last = min($total, $per_page * $current);

        printf(esc_html__('Zobrazuje sa %1$d-%2$d z %3$d produktov', 'graceart'), esc_html((string) $first), esc_html((string) $last), esc_html((string) $total));
    }
?>
</p>
