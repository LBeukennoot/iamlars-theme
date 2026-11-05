<?php
define('VITE_SERVER', 'http://localhost:5173');

function theme_vite_assets() {
    // In local development, pull straight from Vite server
    wp_enqueue_script(
        'vite-client',
        VITE_SERVER . '/@vite/client',
        array(),
        null,
        false
    );

    wp_enqueue_script(
        'theme-main',
        VITE_SERVER . '/src/main.js',
        array('vite-client'),
        null,
        false
    );
}
add_action('wp_enqueue_scripts', 'theme_vite_assets');

// Vite modules require type="module" in the script tag
add_filter('script_loader_tag', function ($tag, $handle) {
    if (in_array($handle, array('vite-client', 'theme-main'), true)) {
        return str_replace('<script ', '<script type="module" ', $tag);
    }
    return $tag;
}, 10, 2);