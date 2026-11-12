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

// Add a manual update trigger link to the theme info modal in wp-admin/themes.php
add_filter('theme_row_meta', function ($meta, $theme_key, $theme, $status) {
    if ($theme_key === 'iamlars-theme' && current_user_can('update_themes')) {
        $check_url = wp_nonce_url(
            admin_url('themes.php?action=check-theme-update&theme=iamlars-theme'),
            'check_theme_update'
        );
        $meta[] = sprintf('<a href="%s" style="color: #2271b1;">Check for updates</a>', esc_url($check_url));
    }
    return $meta;
}, 10, 4);

// Handle the manual check trigger
add_action('admin_init', function () {
    if (
        isset($_GET['action'], $_GET['theme']) &&
        $_GET['action'] === 'check-theme-update' &&
        $_GET['theme'] === 'iamlars-theme'
    ) {
        check_admin_referer('check_theme_update');

        global $myUpdateChecker;
        if (isset($myUpdateChecker)) {
            $myUpdateChecker->checkForUpdates();
        }

        wp_safe_redirect(admin_url('themes.php'));
        exit;
    }
});

add_action('after_setup_theme', function () {
    register_nav_menus([
        'primary_menu' => __('Primary Navigation', 'iamlars-theme'),
        'socials_menu' => __('Socials Navigation', 'iamlars-theme'),
    ]);
});

/**
 * Render inline SVG from src/assets/icons/.
 *
 * @param string $icon_name Filename without extension (e.g. 'github')
 * @param string $class Tailwind classes to inject into the <svg> element
 * @return string Inline SVG markup or empty string
 */
function iamlars_get_icon_svg(string $icon_name, string $class = 'w-5 h-5 fill-current'): string {
    $file_path = get_template_directory() . "/src/assets/icons/{$icon_name}.svg";

    if (!file_exists($file_path)) {
        return '';
    }

    $svg = file_get_contents($file_path);

    // Inject custom Tailwind classes into the root <svg> tag
    if (!empty($class)) {
        if (str_contains($svg, 'class="')) {
            $svg = preg_replace('/class="([^"]*)"/', 'class="$1 ' . esc_attr($class) . '"', $svg, 1);
        } else {
            $svg = preg_replace('/<svg /', '<svg class="' . esc_attr($class) . '" ', $svg, 1);
        }
    }

    return $svg;
}

/**
 * Determine which SVG icon filename to use based on URL or title.
 */
function iamlars_match_social_icon(string $url, string $title = ''): string {
    $url_lower = strtolower($url);
    $title_lower = strtolower($title);

    if (str_contains($url_lower, 'github') || str_contains($title_lower, 'github')) {
        return 'github';
    }
    if (str_contains($url_lower, 'linkedin') || str_contains($title_lower, 'linkedin')) {
        return 'linkedin';
    }
    if (str_contains($url_lower, 'mailto:') || str_contains($title_lower, 'mail') || str_contains($title_lower, 'contact')) {
        return 'mail';
    }
    if (str_contains($url_lower, 'about-me') || str_contains($title_lower, 'about me')) {
        return 'person';
    }
    if (str_contains($url_lower, 'home') || str_contains($title_lower, 'home')) {
        return 'home';
    }

    return 'link'; // Fallback icon name: src/assets/icons/link.svg
}

add_action('init', function () {
    $post_type_object = get_post_type_object('post');
    if ($post_type_object) {
        $post_type_object->template = [
            ['core/post-excerpt', ['moreText' => 'ignore this', 'excerptLength' => 50]],
            ['core/post-terms', ['term' => 'post_tag']],
            // ['iamlars/single-image'], // One of your custom blocks
            // ['core/paragraph', ['placeholder' => 'Start writing your story...']],
        ];
        
        // Optional: Lock the layout so blocks can't be deleted or moved
        // 'all' = cannot add, remove, or move blocks
        // 'insert' = can move existing blocks, cannot add/remove
        // false = completely unlocked (just pre-populated)
        $post_type_object->template_lock = false; 
    }
});