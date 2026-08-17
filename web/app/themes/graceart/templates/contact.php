<?php
/**
 * Template name: Kontakt
 *
 * Title and intro text are edited on the page itself in the block editor; the
 * contact address comes from WooCommerce → Settings → General.
 */

get_header();

while (have_posts()) :
    the_post();

    graceartPageHero(get_the_title(), false);
    ?>

    <div class="section section-padding">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-12">

                    <?php if (get_the_content() !== '') : ?>
                        <div class="graceart-contact-intro"><?php the_content(); ?></div>
                    <?php endif; ?>

                    <div class="contact-form" id="kontakt-formular">
                        <?php echo wp_kses_post(graceartContactNotice()); ?>

                        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                            <input type="hidden" name="action" value="<?php echo esc_attr(GRACEART_CONTACT_ACTION); ?>">
                            <input type="hidden" name="redirect_to" value="<?php echo esc_url(get_permalink()); ?>">
                            <?php wp_nonce_field(GRACEART_CONTACT_ACTION, GRACEART_CONTACT_NONCE); ?>

                            <div class="row learts-mb-n30">
                                <div class="col-md-6 col-12 learts-mb-30">
                                    <input type="text" name="graceart_name" required
                                        placeholder="<?php esc_attr_e('Vaše meno *', 'graceart'); ?>">
                                </div>
                                <div class="col-md-6 col-12 learts-mb-30">
                                    <input type="email" name="graceart_email" required
                                        placeholder="<?php esc_attr_e('Váš e-mail *', 'graceart'); ?>">
                                </div>
                                <div class="col-12 learts-mb-30">
                                    <textarea name="graceart_message" required
                                        placeholder="<?php esc_attr_e('Vaša správa *', 'graceart'); ?>"></textarea>
                                </div>

                                <?php /* Honeypot: hidden from people, tempting to bots. */ ?>
                                <div class="graceart-contact-hp" aria-hidden="true">
                                    <input type="text" name="graceart_website" tabindex="-1" autocomplete="off">
                                </div>

                                <div class="col-12 text-center learts-mb-30">
                                    <button type="submit" class="btn btn-dark btn-outline-hover-dark">
                                        <?php esc_html_e('Odoslať správu', 'graceart'); ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

<?php endwhile; ?>

<?php get_footer(); ?>
