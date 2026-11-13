<a
    class="fixed left-0 m-2 bg-white aspect-square rounded-full z-50 p-3 drop-shadow-2xl text-secondary "
    @pointerdown.stop
    @click.prevent="$store.page.navigateTo('<?php echo esc_url(home_url('/')); ?>')">
    <?= iamlars_get_icon_svg(iamlars_match_social_icon('home'), 'w-5 h-5 sm:w-8 sm:h-8 stroke-secondary clickable') ?>
</a>