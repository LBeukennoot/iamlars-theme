<?php get_header(); ?>

<div class="container mx-auto px-4 pt-24 pb-16">
    <h1 class="mb-10 w-full text-center! lowercase">
        <?php printf(esc_html__('search results for: "%s"', 'iamlars-theme'), '<span>' . esc_html(get_search_query()) . '</span>'); ?>
    </h1>

    <?php if (have_posts()) : ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 justify-items-center">
            <?php
            while (have_posts()) :
                the_post();

                // Format categories to match what x-for="cat in post.categories" expects
                $raw_categories = get_the_category();
                $categories = array_map(function ($cat) {
                    return [
                        'id'   => $cat->term_id,
                        'name' => $cat->name,
                        'slug' => $cat->slug,
                        'link' => get_category_link($cat->term_id),
                    ];
                }, $raw_categories ?: []);

                // Build the exact post object structure expected by card.php
                $post_data = [
                    'id'         => get_the_ID(),
                    'title'      => html_entity_decode(get_the_title(), ENT_QUOTES, 'UTF-8'),
                    'excerpt'    => get_the_excerpt(),
                    'permalink'  => get_permalink(),
                    'image'      => get_the_post_thumbnail_url(get_the_ID(), 'large') ?: 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=800&q=80',
                    'categories' => $categories,
                ];
            ?>
                <div
                    x-data="{
                        post: <?php echo esc_attr(json_encode($post_data)); ?>,
                        index: 0,
                        getCardTransform() {
                            return {};
                        },
                        handleCardClick() {
                            if (this.$store?.page?.navigateTo) {
                                this.$store.page.navigateTo(this.post.permalink);
                            } else {
                                window.location.href = this.post.permalink;
                            }
                        }
                    }"
                    class="relative w-60 sm:w-75 h-85 sm:h-100"
                >
                    <a href="<?php echo esc_url($post_data['permalink']); ?>">
                        <?php get_template_part('template-parts/components/card'); ?>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="flex justify-center mt-12">
            <?php the_posts_pagination([
                'next_text' => '',
            ]); ?>
        </div>
    <?php else : ?>
        <p class="text-center text-slate-500 lowercase">
            <?php esc_html_e('no results found.', 'iamlars-theme'); ?>
        </p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>