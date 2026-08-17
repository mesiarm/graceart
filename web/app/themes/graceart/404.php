<?php

defined('ABSPATH') || exit;

get_header();

graceartPageHero(__('Stránka nenájdená', 'graceart'), false);
?>

<div class="section section-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-12 mx-auto text-center">
                <p class="graceart-404-code">404</p>
                <p class="graceart-404-message"><?php esc_html_e('Ľutujeme, stránka ktorú hľadáte neexistuje alebo bola odstránená.', 'graceart'); ?></p>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-dark btn-hover-primary"><?php esc_html_e('Späť na hlavnú stránku', 'graceart'); ?></a>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
