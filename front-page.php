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
        'categories' => $cat_list,
    );
}, $recent_posts);
?>

<!-- Arc Viewport pinned to bottom -->
<div
    id="ring-gallery-root"
    x-data="ringGallery(<?php echo esc_attr(json_encode($gallery_data)); ?>)"
    x-cloak
    class="relative w-full h-screen flex flex-col items-center justify-around select-none cursor-grab active:cursor-grabbing overflow-visible pt-20 pb-8"

    @pointerdown="onPointerDown($event)"
    @pointermove.window="onPointerMove($event)"
    @pointerup.window="onPointerUp()"
    @pointercancel.window="onPointerUp()">

    <!-- Fixed parent track: The inner wrapper divs take inset-0 to fill this box -->
    <div class="relative w-60 sm:w-75 h-85 sm:h-100">
        <?php foreach ($gallery_data as $index => $post_item) : ?>
            <!-- Alpine transformation wrapper -->
            <div
                class="absolute inset-0"
                :style="getCardTransform(<?php echo (int) $index; ?>)"
                @click="handleCardClick(<?php echo (int) $index; ?>)">
                <?php get_template_part('template-parts/components/card', null, $post_item); ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div
        class="flex flex-col gap-3 lowercase  h-56 sm:h-64 shrink-0 max-w-2xl">
        <h1 class="text-secondary font-display font-bold text-4xl! sm:text-6xl! text-center!">i am lars</h1>
        <h2 class="text-secondary font-sans! font-extralight! text-2xl! sm:text-3xl! text-center! mb-5">creative developer</h2>
        <a
            class="button lowercase transition-opacity duration-500"
            :class="(Math.abs(velocity) > 0.05) ? 'opacity-0 duration-0!' : 'opacity-100'"
            :href="activePost.permalink">
            <span x-text="activePost.title"></span><span class="ml-1.5 arrow-right"></span></a>
    </div>

</div>

<?php get_footer(); ?>