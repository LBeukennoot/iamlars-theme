<?php

if (!defined('ABSPATH')) {
    exit;
}

// 1. Initialize Plugin Update Checker (v6) for Themes
$puc_file = get_template_directory() . '/vendor/plugin-update-checker/plugin-update-checker.php';

if (file_exists($puc_file)) {
    require_once $puc_file;

    if (class_exists('\YahnisElsts\PluginUpdateChecker\v6\PucFactory')) {
        $myUpdateChecker = \YahnisElsts\PluginUpdateChecker\v6\PucFactory::buildUpdateChecker(
            'https://github.com/LBeukennoot/iamlars-theme/',
            get_template_directory(),
            'iamlars-theme'
        );

        $myUpdateChecker->setBranch('main');

        /** @var \YahnisElsts\PluginUpdateChecker\v6\Vcs\GitHubApi|null $vcsApi */
        $vcsApi = method_exists($myUpdateChecker, 'getVcsApi') ? $myUpdateChecker->getVcsApi() : null;

        if ($vcsApi && method_exists($vcsApi, 'enableReleaseAssets')) {
            $vcsApi->enableReleaseAssets();
            if (defined('IAMLARS_GITHUB_TOKEN')) {
                $vcsApi->setAuthentication(IAMLARS_GITHUB_TOKEN);
            }
        }
    }
}

/**
 * Get manifest array.
 */
function get_theme_manifest(): array
{
    static $manifest = null;

    if ($manifest === null) {
        $paths = [
            get_template_directory() . '/dist/.vite/manifest.json',
            get_template_directory() . '/dist/manifest.json',
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                $manifest = json_decode(file_get_contents($path), true) ?: [];
                break;
            }
        }
        if ($manifest === null) {
            $manifest = [];
        }
    }

    return $manifest;
}

/**
 * Manifest helper for JS and CSS files.
 */
function theme_asset(string $entry, string $type = 'file'): string
{
    $manifest = get_theme_manifest();

    // 1. Match in manifest
    if (isset($manifest[$entry][$type])) {
        return 'dist/' . $manifest[$entry][$type];
    }

    // 2. CSS imported inside JS (e.g. import './main.css' inside src/main.js)
    if ($type === 'css' && isset($manifest[$entry]['css'][0])) {
        return 'dist/' . $manifest[$entry]['css'][0];
    }

    // 3. Direct dist fallback (handles unhashed or explicit asset names)
    $filename = basename($entry);
    $target = get_template_directory() . '/dist/assets/' . $filename;
    if (file_exists($target)) {
        return 'dist/assets/' . $filename;
    }

    return '';
}

/**
 * Enqueue frontend scripts and styles from dist/.
 */
add_action('wp_enqueue_scripts', function () {
    $theme_dir = get_template_directory();
    $theme_uri = get_template_directory_uri();

    // 1. Main JavaScript
    $main_js = theme_asset('src/main.js')
        ?: theme_asset('main.js')
        ?: (file_exists($theme_dir . '/dist/assets/main.js') ? 'dist/assets/main.js' : '');

    if ($main_js && file_exists($theme_dir . '/' . $main_js)) {
        wp_enqueue_script(
            'theme-main',
            $theme_uri . '/' . $main_js,
            [],
            filemtime($theme_dir . '/' . $main_js),
            true
        );
    }

    // 2. Main Stylesheet
    $main_css = theme_asset('src/main.css')
        ?: theme_asset('src/main.js', 'css')
        ?: theme_asset('main.css');

    if ($main_css && file_exists($theme_dir . '/' . $main_css)) {
        wp_enqueue_style(
            'theme-main',
            $theme_uri . '/' . $main_css,
            [],
            filemtime($theme_dir . '/' . $main_css)
        );
    }
});

/**
 * Add type="module" to theme scripts.
 */
add_filter('script_loader_tag', function ($tag, $handle) {
    if ($handle === 'theme-main') {
        return str_replace('<script ', '<script type="module" ', $tag);
    }
    return $tag;
}, 10, 2);

/**
 * Theme setup & Gutenberg editor styling.
 */
add_action('after_setup_theme', function () {
    add_theme_support('post-thumbnails');
    add_theme_support('editor-styles');

    $editor_css = theme_asset('src/editor-style.css') ?: theme_asset('editor-style.css');
    if ($editor_css && file_exists(get_template_directory() . '/' . $editor_css)) {
        add_editor_style($editor_css);
    }
});
