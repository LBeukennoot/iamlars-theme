# Creating a new Wordpress Theme with TailwindCSS
While in the theme folder, add these files and folders:
```
styles.css
index.php
header.php
functions.php
footer.php
.gitignore
src/main.css
```

and add this per file:

```css
/* styles.css */
/*
Theme Name: iamlars theme
Theme URI: https://iamlars.nl/
Author: LBeukennoot
Author URI: https://iamlars.nl/
Description: A custom WordPress theme for a personal portfolio
Version: 0.0.1
License: GNU General Public License v2 or later
Text Domain: iamlars-theme
*/
```

```php
<!-- index.php -->
<?php get_header(); ?>

<main class="max-w-4xl mx-auto px-4 py-8">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <article class="mb-8 p-6 bg-white rounded-xl shadow-sm border border-slate-100">
            <h2 class="text-2xl font-bold mb-2">
                <a href="<?php the_permalink(); ?>" class="hover:text-indigo-600 transition-colors">
                    <?php the_title(); ?>
                </a>
            </h2>
            <div class="text-slate-600 leading-relaxed">
                <?php the_excerpt(); ?>
            </div>
        </article>
    <?php endwhile; else : ?>
        <p class="text-slate-500">No posts found.</p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
```

```php
<!-- header.php -->
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class('bg-slate-50 text-slate-900 antialiased'); ?>>
```

```php
<!-- functions.php -->
<?php
function theme_enqueue_scripts() {
    $css_path = get_template_directory() . '/dist/main.css';
    $version  = file_exists($css_path) ? filemtime($css_path) : '1.0.0';

    wp_enqueue_style(
        'theme-tailwind-styles',
        get_template_directory_uri() . '/dist/main.css',
        array(),
        $version
    );
}
add_action('wp_enqueue_scripts', 'theme_enqueue_scripts');

// Add general theme supports
function theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
}
add_action('after_setup_theme', 'theme_setup');


```php
<!-- footer.php -->

<footer class="mt-12 border-t border-slate-200 py-6 text-center text-sm text-slate-500">
        <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All rights reserved.</p>
    </footer>
    <?php wp_footer(); ?>
</body>
</html>
```

```txt
# .gitignore
node_modules/
.DS_Store
```

run `yarn` to initialize the yarn project, then run `yarn add tailwindcss @tailwindcss/cli` and add this to `package.json`:

```json
{
  "name": "iamlars-theme",
  "version": "0.0.1",
  "private": true,
  "scripts": {
    "dev": "npx @tailwindcss/cli -i ./src/main.css -o ./dist/main.css --watch",
    "build": "npx @tailwindcss/cli -i ./src/main.css -o ./dist/main.css --minify"
  },
  "devDependencies": {
  //...
```

start your php server (for example by using Docker) and start Tailwind using `yarn dev`. Open your theme and check if it has styling: this indicates that tailwind is correctly added.