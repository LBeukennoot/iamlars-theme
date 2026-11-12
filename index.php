<?php get_header(); ?>

<?php
$gallery_data = [];

if (have_posts()) :
    while (have_posts()) :
        the_post();

        // 1. Fetch categories for this post
        $categories = get_the_category();

        // 2. Format into clean data for Alpine
        $cat_list = array_map(function ($cat) {
            return [
                'id'   => $cat->term_id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'link' => get_category_link($cat->term_id),
            ];
        }, $categories ?: []);

        $gallery_data[] = [
            'id'         => get_the_ID(),
            'title'      => html_entity_decode(get_the_title(), ENT_QUOTES, 'UTF-8'),
            'permalink'  => get_permalink(),
            'image'      => get_the_post_thumbnail_url(get_the_ID(), 'large') ?: 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=800&q=80',
            'excerpt'    => wp_trim_words(get_the_excerpt(), 10),
            'categories' => $cat_list,
        ];
    endwhile;
endif;

// Determine the archive label (works for tags, categories, or general archives)
$archive_label = single_term_title('', false);
if (empty($archive_label) && !is_home()) {
    $archive_label = wp_strip_all_tags(get_the_archive_title());
}
?>

<?php if (!empty($gallery_data)) : ?>
    <!-- Arc Viewport pinned to bottom -->
    <div
        id="ring-gallery-root"
        x-data="ringGallery(<?php echo esc_attr(json_encode($gallery_data)); ?>)"
        x-cloak
        class="relative w-screen h-screen flex flex-col items-center justify-around select-none cursor-grab active:cursor-grabbing overflow-visible pt-20 pb-8"
        @pointerdown="onPointerDown($event)"
        @pointermove.window="onPointerMove($event)"
        @pointerup.window="onPointerUp()"
        @pointercancel.window="onPointerUp()"
        @wheel.prevent="onWheel($event)">

        <?php if (!empty($archive_label)) : ?>
            <p class="text-secondary font-display text-xl lowercase tracking-wide">
                posts with <?php echo esc_html($archive_label); ?>
            </p>
        <?php endif; ?>

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
                class="text-white bg-primary px-5 py-3 rounded-4xl mx-auto font-sans text-xl cursor-pointer hover:opacity-80 active:scale-90"
                @pointerdown.stop
                @click.prevent="$store.page.navigateTo(activePost.permalink)">read &rarr;</button>
        </div>

    </div>
<?php else : ?>
    <div class="w-full h-screen flex items-center justify-center">
        <p class="text-center text-slate-500 lowercase">
            <?php esc_html_e('no results found.', 'iamlars-theme'); ?>
        </p>
    </div>
<?php endif; ?>

<?php get_footer(); ?>