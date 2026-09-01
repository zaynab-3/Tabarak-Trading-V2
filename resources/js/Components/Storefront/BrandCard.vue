<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight } from '@lucide/vue';
import type { Brand } from '@/types/catalogue';
import { initials } from '@/Utils/format';

defineProps<{ brand: Brand }>();
</script>

<template>
    <Link :href="route('brands.show', brand.slug)" class="group flex min-h-40 items-center gap-5 border border-oat-200 bg-white p-5 transition hover:border-forest-600 hover:shadow-soft">
        <div class="grid size-16 shrink-0 place-items-center overflow-hidden rounded-full bg-oat-100 font-display text-xl font-bold text-forest-800"><img v-if="brand.logo" :src="brand.logo.url" :alt="brand.logo.alt_text || `${brand.name} logo`" class="h-full w-full object-contain p-2" /><span v-else>{{ initials(brand.name) }}</span></div>
        <div class="min-w-0"><h2 class="font-display text-xl font-bold text-forest-900">{{ brand.name }}</h2><p class="mt-1 line-clamp-2 text-sm leading-6 text-slate-500">{{ brand.description }}</p><span class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-forest-700">{{ brand.products_count }} products <ArrowUpRight class="size-3.5" /></span></div>
    </Link>
</template>
