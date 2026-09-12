</main>

<div class="footer2-section section section-padding">
    <div class="container">
        <div class="row learts-mb-n40">

            <div class="col-lg-4 learts-mb-40">
                <div class="widget-about">
                    <img src="<?php echo fullTemplateUri('assets/images/logo/logo.jpg'); ?>" width="463" height="100" alt="Grace Art Logo" class="footer-logo" loading="lazy" decoding="async">
                </div>
            </div>

            <div class="col-lg-4 learts-mb-40">
                <div class="row">
                    <div class="col">
                        <?php /* Vzhľad → Menu → "Dolné menu" */ ?>
                        <?php graceartFooterMenu(); ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 learts-mb-40">
                <ul class="widget-list">
                    <li> <i class="fab fa-facebook-f"></i> <a href="<?php echo esc_url(graceartFacebookUrl()); ?>">Facebook</a></li>
                    <li> <i class="fab fa-instagram"></i> <a href="<?php echo esc_url(graceartInstagramUrl()); ?>">Instagram</a></li>
                </ul>
            </div>

        </div>
    </div>
</div>

<div class="footer2-copyright section">
    <div class="container">
        <p class="copyright text-center">&copy; <?php echo esc_html(gmdate('Y')); ?> <?php echo esc_html(graceartFooterCopyright()); ?></p>
    </div>
</div>
<?php wp_footer(); ?>

</body>

</html>
