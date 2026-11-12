<article
    class="absolute inset-0 bg-primary rounded-3xl overflow-hidden shadow-xl shadow-white/30"
    :style="getCardTransform(index)"
    @click="handleCardClick(index)">

    <div class="w-auto h-2/3 m-2 bg-cover bg-center bg-no-repeat rounded-2xl"
        :style="`background-image: url('${post.image}')`">
        <div class="flex flex-wrap content-end items-end gap-2 ml-1 sm:ml-3 h-full z-10 pointer-events-none">
            <template x-for="cat in post.categories" :key="cat.id">
                <div class="translate-y-1/2"><?php get_template_part('/template-parts/components/tag') ?></div>
            </template>
        </div>
    </div>
    <div class="absolute bottom-0 m-4 sm:m-5">

        <h1 class="font-display text-white! text-3xl! sm:text-5xl! lowercase" x-text="post.title"></h1>
    </div>
</article>