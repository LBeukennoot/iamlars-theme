<?php get_header(); ?>

<main class="max-w-4xl mx-auto px-4 py-8">
    <header class="mb-8 border-b border-slate-200 pb-4">
        <h1 class="text-3xl font-extrabold text-slate-800">
            <?php single_post_title(); ?>
        </h1>
    </header>

    <?php if (have_posts()) : ?>
        <div class="space-y-6">
            <?php while (have_posts()) : the_post(); ?>
                <!-- Loads template-parts/content.php -->
                <?php get_template_part('template-parts/content'); ?>
            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            <?php the_posts_pagination(array(
                'prev_text' => '&larr; Previous',
                'next_text' => 'Next &rarr;',
            )); ?>
        </div>

    <?php else : ?>
        <p class="text-slate-500">No posts found.</p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>