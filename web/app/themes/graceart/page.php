<?php

get_header();

while (have_posts()) :
    the_post();

    graceartPageHero(get_the_title());
    ?>

    <div class="section section-padding">
        <div class="container">
            <?php the_content(); ?>
        </div>
    </div>

<?php endwhile; ?>

<?php get_footer(); ?>
