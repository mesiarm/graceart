<?php

/**
 * Light/dark switch in the header. The choice is kept in the graceart_theme
 * cookie (a year); without it the visitor's system preference decides.
 */

/**
 * Runs first in <head> so the right theme is set before the first paint. The
 * click handler is delegated, so it also covers the toggles printed later.
 */
add_action('wp_head', function (): void {
    ?>
<script id="graceart-theme-init">
(function () {
    var root = document.documentElement;

    function stored() {
        var m = document.cookie.match(/(?:^|; )graceart_theme=(dark|light)/);
        return m ? m[1] : null;
    }

    function apply(theme) {
        root.setAttribute('data-theme', theme);
        var label = theme === 'dark' ? <?php echo wp_json_encode(__('Prepnúť na svetlý režim', 'graceart')); ?> : <?php echo wp_json_encode(__('Prepnúť na tmavý režim', 'graceart')); ?>;
        document.querySelectorAll('.graceart-theme-toggle').forEach(function (btn) {
            btn.setAttribute('aria-label', label);
            btn.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
        });
    }

    var dark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    root.setAttribute('data-theme', stored() || (dark ? 'dark' : 'light'));

    document.addEventListener('DOMContentLoaded', function () {
        apply(root.getAttribute('data-theme'));
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest || !e.target.closest('.graceart-theme-toggle')) {
            return;
        }
        var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        apply(next);
        document.cookie = 'graceart_theme=' + next + '; max-age=31536000; path=/; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
    });
})();
</script>
<?php
    $css = file_get_contents(fullTemplatePath('assets/css/dark-mode.css'));

    if ($css !== false) {
        // Inline so the dark rules are there on first paint, even when the
        // main stylesheets are deferred behind the critical CSS.
        printf('<style id="graceart-dark-mode">%s</style>' . "\n", $css);
    }
}, 0);

function graceartThemeToggle(): void
{
    ?>
<div class="header-theme">
    <button type="button" class="graceart-theme-toggle" aria-label="<?php esc_attr_e('Prepnúť na tmavý režim', 'graceart'); ?>" aria-pressed="false">
        <i class="fas fa-moon icon-to-dark" aria-hidden="true"></i><i class="fas fa-sun icon-to-light" aria-hidden="true"></i>
    </button>
</div>
<?php
}
