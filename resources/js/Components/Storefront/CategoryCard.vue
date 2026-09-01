<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Nut } from '@lucide/vue';
import type { Category } from '@/types/catalogue';

defineProps<{ category: Category }>();
</script>

<template>
    <Link :href="route('categories.show', category.slug)" class="group flex min-h-44 flex-col justify-between border border-oat-200 bg-white p-5 transition hover:border-forest-600 hover:shadow-soft md:min-h-52 md:p-6">
        <div class="flex items-start justify-between gap-4">
            <span class="grid size-12 place-items-center rounded-full bg-forest-50 text-forest-700"><Nut class="size-6" /></span>
            <span class="text-xs font-semibold text-slate-400">{{ category.products_count }} products</span>
        </div>
        <div>
            <h3 class="font-display text-2xl font-bold text-forest-900">{{ category.name }}</h3>
            <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-500">{{ category.description }}</p>
            <span class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-forest-700">Browse category <ArrowRight class="size-4 transition group-hover:translate-x-1" /></span>
        </div>
    </Link>
</template>
