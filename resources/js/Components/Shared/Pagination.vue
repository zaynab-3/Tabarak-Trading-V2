<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { PaginationLink } from '@/types/catalogue';

defineProps<{ links: PaginationLink[] }>();
</script>

<template>
    <nav v-if="links.length > 3" class="flex flex-wrap items-center justify-center gap-1.5" aria-label="Pagination">
        <template v-for="link in links" :key="link.label">
            <Link
                v-if="link.url"
                :href="link.url"
                preserve-scroll
                class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-md border px-3 text-sm font-semibold transition"
                :class="link.active ? 'border-forest-800 bg-forest-800 text-white' : 'border-oat-300 bg-white text-slate-700 hover:border-forest-600'"
                v-html="link.label"
            />
            <span v-else class="inline-flex min-h-10 min-w-10 items-center justify-center px-2 text-sm text-slate-400" v-html="link.label" />
        </template>
    </nav>
</template>
