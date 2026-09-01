<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight, Package } from '@lucide/vue';
import type { Product } from '@/types/catalogue';
import { productPackLabel } from '@/Utils/format';

defineProps<{ product: Product }>();
</script>

<template>
    <article class="group flex h-full flex-col overflow-hidden border border-oat-200 bg-white transition duration-200 hover:-translate-y-0.5 hover:border-oat-300 hover:shadow-soft">
        <Link :href="route('products.show', product.slug)" class="relative block aspect-[4/3] overflow-hidden bg-oat-100">
            <img v-if="product.primary_image" :src="product.primary_image.url" :alt="product.primary_image.alt_text || product.name" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.025]" loading="lazy" />
            <div v-else class="flex h-full flex-col items-center justify-center gap-3 bg-[radial-gradient(circle_at_top,#ffffff_0,#f5f2eb_62%)] text-forest-700">
                <span class="grid size-14 place-items-center rounded-full border border-oat-300 bg-white"><Package class="size-7" /></span>
                <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Image coming soon</span>
            </div>
            <span v-if="product.is_featured" class="absolute left-3 top-3 bg-saffron-500 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white">Featured</span>
        </Link>
        <div class="flex flex-1 flex-col p-4 md:p-5">
            <div class="mb-2 flex items-center gap-2 text-xs font-semibold text-slate-500">
                <span>{{ product.brand?.name || 'Tabarak selection' }}</span><span aria-hidden="true">·</span><span>{{ product.category?.name || 'Catalogue' }}</span>
            </div>
            <h3 class="font-display text-xl font-bold leading-tight text-forest-900">
                <Link :href="route('products.show', product.slug)" class="after:absolute">{{ product.name }}</Link>
            </h3>
            <p class="mt-3 text-sm text-slate-500">{{ productPackLabel(product) }}</p>
            <div class="mt-auto flex items-center justify-between border-t border-oat-200 pt-4 text-sm font-bold text-forest-800">
                <span>View product</span><ArrowUpRight class="size-4 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
            </div>
        </div>
    </article>
</template>
