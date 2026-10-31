<?php

// Generated image sizes are saved as WebP; the uploaded original is left untouched.
add_filter('image_editor_output_format', function (array $formats): array {
    $formats['image/jpeg'] = 'image/webp';
    $formats['image/png'] = 'image/webp';

    return $formats;
});

add_filter('wp_editor_set_quality', function (int $quality, string $mime_type): int {
    return $mime_type === 'image/webp' ? 90 : $quality;
}, 10, 2);
