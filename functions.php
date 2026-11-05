<?php
function theme_enqueue_scripts() {
    $css_file = get_template_directory() . '/dist/main.css';
    $version  = file_exists($css_file) ? filemtime($css_file) : '1.0.0';

    wp_enqueue_style(
        'theme-tailwind-styles',
        get_template_directory_uri() . '/dist/main.css',
        array(),
        $version
    );
}
add_action('wp_enqueue_scripts', 'theme_enqueue_scripts');

function theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
}
add_action('after_setup_theme', 'theme_setup');