<?php
// Retrieve assigned navigation menus
$locations       = get_nav_menu_locations();
$primary_menu_id = $locations['primary_menu'] ?? null;
$primary_items   = $primary_menu_id ? wp_get_nav_menu_items($primary_menu_id) : [];

$socials_menu_id = $locations['socials_menu'] ?? null;
$social_items    = $socials_menu_id ? wp_get_nav_menu_items($socials_menu_id) : [];
?>

<div class="absolute left-1/2 -translate-x-1/2 flex gap-2 z-50 mt-2 [&>div]:flex [&>div]:gap-5 [&>div]:px-5 [&>div]:py-3 [&>div]:rounded-full [&>div]:bg-white items-center drop-shadow-2xl">
    <!-- 1. Primary Menu & Expanding Search -->
    <div class="items-center">
        <!-- Dynamic Primary Menu Links (e.g. About Me) -->
        <?php if (!empty($primary_items)) : ?>
            <?php foreach ($primary_items as $item) : ?>
                <a 
                    href="<?= esc_url($item->url); ?>" 
                    title="<?= esc_attr($item->title); ?>"
                    aria-label="<?= esc_attr($item->title); ?>"
                    class="text-secondary hover:text-primary transition-colors flex items-center justify-center"
                >
                    <?= $item->title !== "about me" ? esc_html($item->title) : iamlars_get_icon_svg(iamlars_match_social_icon($item->url, $item->title), 'w-8 h-8 stroke-secondary') ?>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Search Component with Expanding Input -->
        <div 
            x-data="{ open: false }" 
            @click.outside="open = false" 
            @keydown.escape.window="open = false"
            class="flex items-center"
        >
            <form role="search" method="get" action="<?= esc_url(home_url('/')); ?>" class="flex items-center">
                <!-- Search Toggle / Submit Button -->
                <button 
                    type="button" 
                    @click="open = !open; if(open) { $nextTick(() => $refs.searchInput.focus()) }"
                    aria-label="Toggle search"
                    class="text-primary hover:text-primary transition-colors focus:outline-none flex items-center cursor-pointer"
                >
                    <?= iamlars_get_icon_svg('search', 'w-8 h-8 stroke-current'); ?>
                </button>

                <!-- Input field that expands to the right -->
                <div 
                    x-show="open"
                    x-cloak
                    x-transition:enter="transition-all  duration-300"
                    x-transition:enter-start="w-0 opacity-0"
                    x-transition:enter-end="w-44 opacity-100"
                    x-transition:leave="transition-all  duration-200"
                    x-transition:leave-start="w-44 opacity-100"
                    x-transition:leave-end="w-0 opacity-0"
                    class="overflow-hidden flex items-center h-full"
                >
                    <input 
                        x-ref="searchInput"
                        type="search" 
                        name="s" 
                        placeholder="Search..." 
                        class="h-full ml-2 px-2.5 text-sm bg-white text-primary border-0 focus:ring-0 focus:outline-none w-40"
                    />
                </div>
            </form>
        </div>
    </div>

    <!-- 2. Socials Menu with SVGs -->
    <div class="items-center">
        <?php if (!empty($social_items)) : ?>
            <?php foreach ($social_items as $item) : ?>
                <?php 
                    $icon_slug = iamlars_match_social_icon($item->url, $item->title); 
                ?>
                <a 
                    href="<?= esc_url($item->url); ?>" 
                    target="_blank" 
                    rel="noopener noreferrer" 
                    title="<?= esc_attr($item->title); ?>"
                    aria-label="<?= esc_attr($item->title); ?>"
                    class="text-secondary hover:text-primary transition-colors flex items-center justify-center"
                >
                    <?= iamlars_get_icon_svg($icon_slug, 'w-8 h-8 stroke-current'); ?>
                </a>
            <?php endforeach; ?>
        <?php else : ?>
            <span class="text-xs text-gray-400">Assign Socials</span>
        <?php endif; ?>
    </div>
</div>