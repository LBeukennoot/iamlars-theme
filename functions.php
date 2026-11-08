<?php

if (!defined('ABSPATH')) {
    exit;
}

// 1. Always use absolute paths with __DIR__ to prevent path resolution crashes
$puc_file = __DIR__ . '/vendor/plugin-update-checker/plugin-update-checker.php';

if (file_exists($puc_file)) {
    require_once $puc_file;

    // 2. Build the update checker
    $myUpdateChecker = \YahnisElsts\PluginUpdateChecker\v6\PucFactory::buildUpdateChecker(
        'https://github.com/LBeukennoot/iamlars-theme/',
        __FILE__,
        'iamlars-theme'
    );

    // Set the stable branch
    $myUpdateChecker->setBranch('main');

    // 3. Type-hint to satisfy the IDE and guard against null VCS API
    /** @var \YahnisElsts\PluginUpdateChecker\v6\Vcs\GitHubApi|null $vcsApi */
    $vcsApi = method_exists($myUpdateChecker, 'getVcsApi') ? $myUpdateChecker->getVcsApi() : null;

    if ($vcsApi && method_exists($vcsApi, 'enableReleaseAssets')) {
        $vcsApi->enableReleaseAssets();

        // 4. Authenticate private repository (Required for private GitHub repos)
        // Pass your GitHub Personal Access Token (classic) with 'repo' scope
        // $vcsApi->setAuthentication('YOUR_GITHUB_PERSONAL_ACCESS_TOKEN');
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

    // Try Docker host gateway first, then localhost fallback
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
 * Manifest helper for production builds.
 */
function theme_asset(string $entry): string {
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
    }

    if (isset($manifest[$entry]['file'])) {
        return 'dist/' . $manifest[$entry]['file'];
    }

    return '';
}

/**
 * Enqueue frontend scripts and styles (Dev vs. Prod).
 */
add_action('wp_enqueue_scripts', function () {
    if (is_vite_dev()) {
        // --- DEV ENVIRONMENT (Vite HMR) ---
        wp_enqueue_script('vite-client', VITE_SERVER . '/@vite/client', [], null, false);
        wp_enqueue_script('theme-main', VITE_SERVER . '/src/main.js', ['vite-client'], null, false);
    } else {
        // --- PROD ENVIRONMENT (dist/ files) ---
        $main_js = theme_asset('src/main.js');
        if ($main_js) {
            wp_enqueue_script('theme-main', get_template_directory_uri() . '/' . $main_js, [], null, true);
        }

        // Check if Vite generated a linked main.css
        $main_css = theme_asset('src/main.css') ?: theme_asset('src/input.css');
        if (!$main_css && file_exists(get_template_directory() . '/dist/assets/main.css')) {
            $main_css = 'dist/assets/main.css';
        }

        if ($main_css) {
            wp_enqueue_style('theme-main', get_template_directory_uri() . '/' . $main_css, [], null);
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

    // In prod or dev, point to the built editor CSS
    $editor_css = 'dist/assets/editor-style.css';
    if (file_exists(get_template_directory() . '/' . $editor_css)) {
        add_editor_style($editor_css);
    }
});

/**
 * Enqueue editor style directly in Gutenberg when in Dev mode.
 */
add_action('enqueue_block_editor_assets', function () {
    if (is_vite_dev()) {
        // In dev, load editor CSS via Vite for instant updates
        wp_enqueue_style(
            'theme-editor-dev',
            VITE_SERVER . '/src/editor-style.css',
            [],
            null
        );
    }
});