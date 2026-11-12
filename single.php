<?php get_header(); ?>

<div class="flex flex-col gap-5 transition-opacity duration-500 max-w-2xl w-full mt-[25vh]">
    <h1 class="text-secondary font-display font-bold text-6xl mx-auto lowercase text-center"><?php echo get_the_title(); ?></h1>

    <div class="prose prose-invert prose-primary max-w-2xl mx-auto entry-content flex flex-col gap-10 w-full">
        <?php the_content(); ?>
    </div>
</div>
</div>

<div>
    <h2 class="mt-20 mb-5 w-full text-center!">more projects</h2>

    <div class="flex w-full justify-center">
        <div class="relative w-60 sm:w-75 h-85 sm:h-100">
            <?php
            $random_query = new WP_Query([
                'post_type'           => 'post',
                'posts_per_page'      => 1,
                'orderby'             => 'rand',
                'post_status'         => 'publish',
                'ignore_sticky_posts' => true,
                'post__not_in'        => is_singular('post') ? [get_the_ID()] : [],
            ]);

            if ($random_query->have_posts()) :
                while ($random_query->have_posts()) :
                    $random_query->the_post();

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
                    $random_post_data = [
                        'id'         => get_the_ID(),
                        'title'      => html_entity_decode(get_the_title(), ENT_QUOTES, 'UTF-8'),
                        'excerpt'    => get_the_excerpt(),
                        'permalink'  => get_permalink(),
                        'image'      => get_the_post_thumbnail_url($post, 'large') ?: 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=800&q=80',
                        'categories' => $categories,
                    ];
            ?>

                    <!-- Provide the expected 'post' scope and safe no-ops for ringGallery helpers -->
                    <div
                        x-data="{
                post: <?php echo esc_attr(json_encode($random_post_data)); ?>,
                index: 0,
                getCardTransform() { return {}; },
                handleCardClick() {
                    if (this.$store?.page?.navigateTo) {
                        this.$store.page.navigateTo(this.post.permalink);
                    } else {
                        window.location.href = this.post.permalink;
                    }
                }
            }"
                        class="relative w-60 sm:w-75 h-85 sm:h-100">
                        <a href="<?php echo $random_post_data['permalink'] ?>">
                            <?php get_template_part('template-parts/components/card'); ?>
                        </a>
                    </div>

            <?php
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>