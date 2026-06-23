<?php

get_header();

while (have_posts()) :
    the_post();

    $graceart_page_title = get_the_title();

    if (function_exists('graceartLocalizeWooPageLabel')) {
        $graceart_page_title = graceartLocalizeWooPageLabel($graceart_page_title);
    }

    graceartPageHero($graceart_page_title);
    ?>

    <div class="section section-padding">
        <div class="container">
            <?php the_content(); ?>
        </div>
    </div>

<?php endwhile; ?>

<?php get_footer(); ?>
