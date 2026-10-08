<?php

/**
 * Complianz keeps its banner texts in the database, but a few link labels are
 * fixed strings the Slovak language pack does not translate.
 */
add_filter('gettext', function (string $translated, string $text, string $domain): string {
    if ($domain !== 'complianz-gdpr') {
        return $translated;
    }

    $slovak = [
        'Manage options' => 'Spravovať možnosti',
        'Manage services' => 'Spravovať služby',
    ];

    return $slovak[$text] ?? $translated;
}, 10, 3);
