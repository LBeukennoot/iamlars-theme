<?php get_header(); ?>

<div class="flex flex-col gap-5 transition-opacity duration-500 max-w-2xl w-full mt-[25vh]">
    <h1 class="text-secondary font-display font-bold text-6xl mx-auto lowercase text-center"><?php echo get_the_title(); ?></h1>

    <div class="prose prose-invert prose-primary max-w-2xl mx-auto entry-content flex flex-col gap-10 w-full">
        <?php the_content(); ?>
    </div>
</div>

<?php get_footer(); ?>
