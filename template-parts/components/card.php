<?php
// Use passed $args, or fallback to standard WordPress loop functions if used outside a custom array
$post_id    = $args['id'] ?? get_the_ID();
$title      = $args['title'] ?? get_the_title($post_id);
$image      = $args['image'] ?? (get_the_post_thumbnail_url($post_id, 'large') ?: 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=800&q=80');
$categories = $args['categories'] ?? get_the_category($post_id);
?>

<article
    class="relative w-full h-full bg-primary bg-cover bg-center rounded-3xl overflow-hidden shadow-xl shadow-white/30"
    style="background-image: url('<?php echo esc_url($image); ?>');">
    <div class="absolute bottom-0 m-4 sm:m-5">
        <?php if (!empty($categories)) : ?>
            <div class="flex flex-wrap content-end items-end gap-2 h-full z-10 pointer-events-none">
                <?php foreach ($categories as $cat) : 
                    // Support both Alpine-formatted arrays and standard WP_Term objects
                    $cat_name = is_object($cat) ? $cat->name : ($cat['name'] ?? $cat);
                ?>
                    <div class="drop-shadow-sm">
                        <?php get_template_part('template-parts/components/tag', null, ['name' => $cat_name]); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <h1 class="font-display text-white! text-3xl! sm:text-5xl! lowercase drop-shadow-md">
            <?php echo esc_html($title); ?>
        </h1>
    </div>
</article>