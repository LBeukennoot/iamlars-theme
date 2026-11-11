<article
    class="absolute inset-0 bg-primary rounded-3xl overflow-hidden shadow-xl shadow-white/30"
    :style="getCardTransform(index)"
    @click="handleCardClick(index)">

    <div class="w-auto h-2/3 m-2 bg-cover bg-center bg-no-repeat rounded-2xl"
        :style="`background-image: url('${post.image}')`">
        <div class="flex ml-3 h-full items-end flex-wrap gap-1.5 z-10 pointer-events-none">
            <template x-for="cat in post.categories" :key="cat.id">
                <div class="translate-y-1/2"><?php get_template_part('/template-parts/components/tag') ?></div>
                <!-- <span
                    x-text="cat.name"
                    class="px-2.5 py-1 text-[11px] font-medium tracking-wide text-white/90 bg-slate-900/70 backdrop-blur-md border border-white/10 rounded-full shadow-sm"></span> -->
            </template>
        </div>
    </div>
    <div class="absolute bottom-0 m-5">

        <h1 class="font-display text-white! text-5xl! lowercase" x-text="post.title"></h1>
    </div>
    <!-- <img
        :src="post.image"
        :alt="post.title"
        class="w-full h-52 object-cover pointer-events-none shadow-inner shadow-primary/30" /> -->
    <!-- <div class="p-6 flex flex-col justify-between h-[calc(400px-208px)]">
        <div>
            <h3 class="text-lg font-bold text-white line-clamp-1 group-hover:text-primary transition-colors font-display lowercase" x-text="post.title"></h3>
            <p class="text-xs text-white/60 line-clamp-2 mt-1 leading-relaxed" x-text="post.excerpt"></p>
        </div>
        <a
            :href="post.permalink"
            @pointerdown.stop
            class="inline-flex items-center justify-center text-xs font-semibold py-2.5 px-4 rounded-xl bg-primary hover:brightness-110 text-white transition-all shadow-md shadow-primary/20">
            View Project &rarr;
        </a>
    </div> -->
</article>