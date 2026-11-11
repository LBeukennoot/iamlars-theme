<?php get_header(); ?>

<?php
$recent_posts = get_posts(array(
    'numberposts' => 8,
    'post_status' => 'publish',
));

$gallery_data = array_map(function ($post) {
    // 1. Fetch categories for this post
    $categories = get_the_category($post->ID);

    // 2. Format into clean data for Alpine
    $cat_list = array_map(function ($cat) {
        return array(
            'id'    => $cat->term_id,
            'name'  => $cat->name,
            'slug'  => $cat->slug,
            'link'  => get_category_link($cat->term_id),
        );
    }, $categories ?: array());

    return array(
        'id'         => $post->ID,
        'title'      => get_the_title($post),
        'permalink'  => get_permalink($post),
        'image'      => get_the_post_thumbnail_url($post, 'large') ?: 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=800&q=80',
        'excerpt'    => wp_trim_words(get_the_excerpt($post), 10),
        'categories' => $cat_list, // <-- Added here
    );
}, $recent_posts);
?>

<!-- Arc Viewport pinned to bottom -->
<div
    id="ring-gallery-root"
    x-data="ringGallery(<?php echo esc_attr(json_encode($gallery_data)); ?>)"
    x-cloak
    class="relative w-full h-screen flex flex-col items-center justify-around select-none cursor-grab active:cursor-grabbing overflow-visible pt-20 pb-8 "

    @pointerdown="onPointerDown($event)"
    @pointermove.window="onPointerMove($event)"
    @pointerup.window="onPointerUp()"
    @pointercancel.window="onPointerUp()">

    <div class="relative w-60 sm:w-75 h-85 sm:h-100">
        <template x-for="(post, index) in posts" :key="post.id">
            <?php get_template_part('template-parts/components/card'); ?>
        </template>
    </div>

    <div
        class="flex flex-col gap-3 lowercase transition-opacity duration-500 h-56 sm:h-64 shrink-0 max-w-2xl"
        :class="(Math.abs(velocity) > 0.05) ? 'opacity-0 duration-0!' : 'opacity-100'">
        <h1 class="text-secondary font-display font-bold text-4xl! sm:text-6xl! text-center! line-clamp-1 pb-1.5 sm:pb-5" x-text="activePost.title"></h1>
        <h2 class="text-secondary font-sans! font-extralight! text-2xl! sm:text-3xl! text-center! line-clamp-2 leading-snug" x-text="activePost.excerpt"></h2>
        <button
            class="text-white bg-primary px-5 py-3 rounded-4xl mx-auto font-sans text-xl cursor-pointer hover:opacity-80"
            :href="activePost.permalink"
            @pointerdown.stop
            @click.prevent="$store.page.navigateTo(activePost.permalink)">read &rarr;</button>
    </div>

</div>

<?php get_footer(); ?>