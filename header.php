<!DOCTYPE html>
<html <?php language_attributes(); ?> class="<?php echo is_front_page() ? 'max-h-screen overflow-hidden' : ''; ?>">

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>

<body <?php body_class('antialiased'); ?> x-data @scroll.window.passive="$store.page.handleScroll()">

    <main class="relative w-full min-h-screen flex flex-col justify-between overflow-x-clip">

        <!-- Fixed Backgrounds (stay in place while scrolling) -->
        <div class="absolute inset-0 pointer-events-none -z-10 bg-[radial-gradient(var(--color-primary)_1px,transparent_1px)] bg-size-[30px_30px]" aria-hidden="true"></div>

        <div class="fixed [&>div]:absolute [&>div]:size-11/12 [&>div]:rounded-full [&>div]:bg-primary [&>div]:blur-4xl inset-0 pointer-events-none -z-10 overflow-hidden" aria-hidden="true">
            <div :style="`transform: translate3d(0, -${$store.page.scroll}px, 0);`" class="-top-1/2 left-3/4"></div>
            <div :style="`transform: translate3d(0, -${$store.page.scroll}px, 0);`" class="-top-1/2 -left-3/4"></div>
            <div :style="`transform: translate3d(0, -${$store.page.scroll}px, 0);`" class="-top-2/3 left-1/4"></div>
            <div :style="`transform: translate3d(0, -${$store.page.scroll}px, 0);`" class="-top-2/3 -left-1/4"></div>
        </div>

        <div class="absolute inset-0 pointer-events-none -z-10 bg-[radial-gradient(rgba(255,255,255,0.45)_1px,transparent_1px)] bg-size-[30px_30px]" aria-hidden="true"></div>

        <!-- Foreground Content Wrapper -->
        <div
            class="relative z-10 w-full h-full transition-opacity px-4 flex justify-center"
            :class="$store.page.isLoading ? 'opacity-0!' : 'opacity-100!'">