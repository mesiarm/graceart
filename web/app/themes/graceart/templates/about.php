<?php
/**
 * Template name: O mne
 *
 * Prose (and any Gallery block) is edited in the normal WordPress block editor.
 */

get_header();

while (have_posts()) :
    the_post();

    graceartPageHero(get_the_title(), false);
    ?>

    <div class="section section-padding">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-9 col-lg-10">
                    <div class="graceart-about-content">
                        <?php the_content(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php endwhile; ?>

<?php get_footer(); ?>
