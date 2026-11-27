<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { PaginationLink } from '@/types/catalogue';

defineProps<{ links: PaginationLink[]; storefront?: boolean }>();
</script>

<template>
    <nav v-if="links.length > 3" class="flex flex-wrap items-center justify-center gap-1" aria-label="Pagination">
        <template v-for="link in links" :key="link.label">
            <Link
                v-if="link.url"
                :href="link.url"
                :preserve-scroll="true"
                :preserve-state="true"
                :only="storefront ? ['products', 'filters', 'brands'] : []"
                class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-full px-3 text-sm font-bold transition"
                :class="link.active ? 'bg-tabarak-ink text-white' : 'text-slate-500 hover:bg-[#ECEAE5] hover:text-tabarak-ink'"
                v-html="link.label"
            />
            <span v-else class="inline-flex min-h-10 min-w-10 items-center justify-center px-2 text-sm text-slate-300" v-html="link.label" />
        </template>
    </nav>
</template>
