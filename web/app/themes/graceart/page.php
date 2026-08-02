<?php

get_header();

while (have_posts()) :
    the_post();

    $graceart_page_title = get_the_title();

    if (function_exists('graceartLocalizeWooPageLabel')) {
        $graceart_page_title = graceartLocalizeWooPageLabel($graceart_page_title);
    }

    $graceart_show_breadcrumb = ! (function_exists('is_cart') && is_cart()) && ! (function_exists('is_checkout') && is_checkout());

    graceartPageHero($graceart_page_title, $graceart_show_breadcrumb);
    ?>

    <div class="section section-padding">
        <div class="container">
            <?php the_content(); ?>
        </div>
    </div>

<?php endwhile; ?>

<?php get_footer(); ?>
