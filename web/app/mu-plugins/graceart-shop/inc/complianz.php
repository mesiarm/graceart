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
        'Always active' => 'Vždy aktívne',
        'Close dialog' => 'Zavrieť okno',
        'Manage options' => 'Spravovať možnosti',
        'Manage services' => 'Spravovať služby',
        'Read more about these purposes' => 'Prečítajte si viac o týchto účeloch',
        'Read more about TCF purposes on Cookie Database' => 'Prečítajte si viac o účeloch TCF v databáze cookies',
    ];

    return $slovak[$text] ?? $translated;
}, 10, 3);
