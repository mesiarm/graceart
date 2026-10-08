<?php
/**
 * Template name: Vrátenie tovaru
 *
 * Information on the consumer's right of withdrawal (zákon č. 108/2024 Z. z.),
 * and the model withdrawal form. Seller details come
 * from WooCommerce → Settings → General; anything typed into the page itself
 * in the block editor is shown above the standard text.
 */

get_header();

$email = graceartContactEmail();
$address = graceartCompanyAddressLines();
$ico = graceartCompanyIco();
$shop = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);

while (have_posts()) :
    the_post();

    graceartPageHero(get_the_title(), false);
    ?>

    <div class="section section-padding">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-12 graceart-returns">

                    <?php if (get_the_content() !== '') : ?>
                        <div class="graceart-contact-intro"><?php the_content(); ?></div>
                    <?php endif; ?>

                    <h2><?php esc_html_e('Právo odstúpiť od zmluvy', 'graceart'); ?></h2>
                    <p><?php esc_html_e('Ako spotrebiteľ máte právo odstúpiť od zmluvy uzavretej na diaľku do 14 dní bez udania dôvodu. Lehota začína plynúť dňom prevzatia tovaru. Ak je objednávka doručená vo viacerých zásielkach, plynie odo dňa prevzatia poslednej z nich.', 'graceart'); ?></p>
                    <p><?php esc_html_e('Aby ste právo na odstúpenie dodržali, stačí odoslať oznámenie o odstúpení pred uplynutím lehoty. Môžete použiť vzorový formulár nižšie alebo akékoľvek jednoznačné vyhlásenie zaslané e-mailom či poštou.', 'graceart'); ?></p>

                    <h3><?php esc_html_e('Predávajúci', 'graceart'); ?></h3>
                    <p>
                        <strong><?php echo esc_html($shop); ?></strong><br>
                        <?php foreach ($address as $line) : ?>
                            <?php echo esc_html($line); ?><br>
                        <?php endforeach; ?>
                        <?php if ($ico !== '') : ?>
                            <?php esc_html_e('IČO:', 'graceart'); ?> <?php echo esc_html($ico); ?><br>
                        <?php endif; ?>
                        <?php if ($email !== '') : ?>
                            <?php esc_html_e('E-mail:', 'graceart'); ?> <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                        <?php endif; ?>
                    </p>

                    <h3><?php esc_html_e('Ako vrátiť tovar', 'graceart'); ?></h3>
                    <ol>
                        <li><?php esc_html_e('Oznámte nám odstúpenie od zmluvy e-mailom alebo listom (môžete použiť vzorový formulár nižšie).', 'graceart'); ?></li>
                        <li><?php esc_html_e('Tovar nám pošlite späť alebo odovzdajte najneskôr do 14 dní od odstúpenia, na adresu predávajúceho uvedenú vyššie. Pribaľte číslo objednávky.', 'graceart'); ?></li>
                        <li><?php esc_html_e('Tovar zabaľte tak, aby sa pri preprave nepoškodil. Odporúčame zásielku poistiť.', 'graceart'); ?></li>
                    </ol>

                    <h3><?php esc_html_e('Vrátenie platby', 'graceart'); ?></h3>
                    <p><?php esc_html_e('Všetky platby, ktoré sme od vás prijali, vrátane nákladov na dopravu k vám, vám vrátime do 14 dní od doručenia oznámenia o odstúpení. Vrátime ich rovnakým spôsobom, akým ste platili, ak sa nedohodneme inak. Nevzniknú vám tým žiadne ďalšie náklady. Pri doprave vrátime najlacnejší štandardný spôsob dopravy, ktorý sme ponúkali.', 'graceart'); ?></p>
                    <p><?php esc_html_e('Platbu môžeme zadržať, kým nám tovar nevrátite alebo neposkytnete doklad o jeho odoslaní, podľa toho, čo nastane skôr.', 'graceart'); ?></p>

                    <h3><?php esc_html_e('Náklady a stav tovaru', 'graceart'); ?></h3>
                    <p><?php esc_html_e('Náklady na vrátenie tovaru znášate vy. Za zníženie hodnoty tovaru, ku ktorému došlo takým zaobchádzaním, ktoré presahuje rámec nevyhnutný na zistenie povahy, vlastností a funkčnosti tovaru, zodpovedáte len vy.', 'graceart'); ?></p>

                    <h3><?php esc_html_e('Kedy odstúpiť nemožno', 'graceart'); ?></h3>
                    <p><?php esc_html_e('Od zmluvy nemožno odstúpiť pri:', 'graceart'); ?></p>
                    <ul>
                        <li><?php esc_html_e('tovare vyrobenom podľa osobitných požiadaviek spotrebiteľa alebo upravenom na mieru (zákazkové diela),', 'graceart'); ?></li>
                        <li><?php esc_html_e('tovare, ktorý podlieha rýchlej skaze alebo má krátku lehotu spotreby,', 'graceart'); ?></li>
                        <li><?php esc_html_e('tovare v uzavretom obale, ktorý sa z hygienických dôvodov nedá vrátiť a obal bol po dodaní porušený,', 'graceart'); ?></li>
                        <li><?php esc_html_e('tovare, ktorý bol po dodaní nerozlučne zmiešaný s iným tovarom.', 'graceart'); ?></li>
                    </ul>

                    <h3><?php esc_html_e('Vzorový formulár na odstúpenie od zmluvy', 'graceart'); ?></h3>
                    <div class="graceart-returns__model">
                        <p><em><?php esc_html_e('(vyplňte a zašlite tento formulár iba vtedy, ak chcete odstúpiť od zmluvy)', 'graceart'); ?></em></p>
                        <p>
                            <?php esc_html_e('Adresát:', 'graceart'); ?>
                            <?php echo esc_html(implode(', ', array_filter(array_merge([$shop], $address, [$email])))); ?>
                        </p>
                        <p><?php esc_html_e('Oznamujem, že odstupujem od zmluvy o kúpe tohto tovaru:', 'graceart'); ?> …………………………</p>
                        <p><?php esc_html_e('Dátum objednania / dátum prijatia:', 'graceart'); ?> …………………………</p>
                        <p><?php esc_html_e('Meno a priezvisko spotrebiteľa:', 'graceart'); ?> …………………………</p>
                        <p><?php esc_html_e('Adresa spotrebiteľa:', 'graceart'); ?> …………………………</p>
                        <p><?php esc_html_e('Podpis spotrebiteľa (iba ak sa formulár podáva v listinnej podobe):', 'graceart'); ?> …………………………</p>
                        <p><?php esc_html_e('Dátum:', 'graceart'); ?> …………………………</p>
                    </div>

                    <p class="text-center">
                        <a href="<?php echo esc_url(fullTemplateUri('assets/docs/formular-na-odstupenie-od-zmluvy.pdf')); ?>" class="btn btn-dark btn-outline-hover-dark" download>
                            <?php esc_html_e('Stiahnuť formulár (PDF)', 'graceart'); ?>
                        </a>
                    </p>

                    <p class="graceart-returns__complaints">
                        <?php esc_html_e('Reklamácie vadného tovaru (záručná doba 24 mesiacov) sú samostatný postup upravený v reklamačnom poriadku, ktorý je súčasťou obchodných podmienok.', 'graceart'); ?>
                    </p>

                </div>
            </div>
        </div>
    </div>

<?php endwhile; ?>

<?php get_footer(); ?>
