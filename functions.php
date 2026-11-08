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
            get_template_directory(), // Note: Use directory path for themes, not __FILE__
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

define('VITE_SERVER', 'http://localhost:5173');

/**
 * Check if the Vite dev server is running.
 */
function is_vite_dev(): bool {
    static $is_dev = null;

    if ($is_dev !== null) {
        return $is_dev;
    }

    if (defined('THEME_DEV') && !THEME_DEV) {
        return $is_dev = false;
    }

    $hosts = ['host.docker.internal', 'localhost', '127.0.0.1'];
    foreach ($hosts as $host) {
        $connection = @fsockopen($host, 5173, $errno, $errstr, 0.05);
        if (is_resource($connection)) {
            fclose($connection);
            return $is_dev = true;
        }
    }

    return $is_dev = false;
}

/**
 * Get manifest array.
 */
function get_theme_manifest(): array {
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
function theme_asset(string $entry, string $type = 'file'): string {
    $manifest = get_theme_manifest();

    // 1. Direct match in manifest
    if (isset($manifest[$entry][$type])) {
        return 'dist/' . $manifest[$entry][$type];
    }

    // 2. If looking for CSS that was imported inside a JS entrypoint (e.g. import './main.css' inside src/main.js)
    if ($type === 'css' && isset($manifest[$entry]['css'][0])) {
        return 'dist/' . $manifest[$entry]['css'][0];
    }

    // 3. Fallback: Search dist directory if manifest is missing or non-hashed names were used
    $filename = basename($entry);
    $matches = glob(get_template_directory() . '/dist/assets/' . pathinfo($filename, PATHINFO_FILENAME) . '*.' . pathinfo($filename, PATHINFO_EXTENSION));
    if (!empty($matches)) {
        return 'dist/assets/' . basename($matches[0]);
    }

    return '';
}

/**
 * Enqueue frontend scripts and styles.
 */
add_action('wp_enqueue_scripts', function () {
    if (is_vite_dev()) {
        // --- DEV ENVIRONMENT (Vite HMR) ---
        wp_enqueue_script('vite-client', VITE_SERVER . '/@vite/client', [], null, false);
        wp_enqueue_script('theme-main', VITE_SERVER . '/src/main.js', ['vite-client'], null, false);
    } else {
        // --- PROD ENVIRONMENT (dist/ files) ---
        // 1. Enqueue JS
        $main_js = theme_asset('src/main.js');
        if ($main_js && file_exists(get_template_directory() . '/' . $main_js)) {
            wp_enqueue_script(
                'theme-main',
                get_template_directory_uri() . '/' . $main_js,
                [],
                filemtime(get_template_directory() . '/' . $main_js),
                true
            );
        }

        // 2. Enqueue CSS (checks src/main.css entry, CSS emitted by src/main.js, and physical dist fallback)
        $main_css = theme_asset('src/main.css') 
            ?: theme_asset('src/main.js', 'css')
            ?: theme_asset('main.css');

        if ($main_css && file_exists(get_template_directory() . '/' . $main_css)) {
            wp_enqueue_style(
                'theme-main',
                get_template_directory_uri() . '/' . $main_css,
                [],
                filemtime(get_template_directory() . '/' . $main_css)
            );
        }
    }
});

/**
 * Add type="module" to Vite scripts.
 */
add_filter('script_loader_tag', function ($tag, $handle) {
    if (in_array($handle, ['vite-client', 'theme-main'], true)) {
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

    // Resolve editor CSS via manifest or fallback
    $editor_css = theme_asset('src/editor-style.css') ?: theme_asset('editor-style.css');
    if ($editor_css && file_exists(get_template_directory() . '/' . $editor_css)) {
        add_editor_style($editor_css);
    }
});

/**
 * Enqueue editor style directly in Gutenberg when in Dev mode.
 */
add_action('enqueue_block_editor_assets', function () {
    if (is_vite_dev()) {
        wp_enqueue_style(
            'theme-editor-dev',
            VITE_SERVER . '/src/editor-style.css',
            [],
            null
        );
    }
});