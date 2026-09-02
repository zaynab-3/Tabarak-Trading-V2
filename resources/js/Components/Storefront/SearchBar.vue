<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { Search, X } from '@lucide/vue';
import { computed, onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps<{ initial?: string; compact?: boolean }>();
const page = usePage();
const pageFilters = computed(() => (page.props.filters as { search?: string } | undefined)?.search ?? '');
const query = ref(props.initial ?? pageFilters.value);
const inputId = props.compact ? 'mobile-catalogue-search' : 'desktop-catalogue-search';

let timeout: ReturnType<typeof setTimeout> | null = null;

const performSearch = (val: string) => {
    const currentParams = new URLSearchParams(window.location.search);
    const trimmed = val.trim();

    if (trimmed) {
        currentParams.set('search', trimmed);
    } else {
        currentParams.delete('search');
    }
    currentParams.delete('page');

    const isStorefront = route().current('shop') || route().current('search');
    const targetRoute = isStorefront ? (route().current('shop') ? route('shop') : route('search')) : route('shop');
    const queryObj = Object.fromEntries(currentParams.entries());

    router.get(targetRoute, queryObj, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        showProgress: false,
        only: ['products', 'filters', 'brands'],
    });
};

const handleInput = () => {
    if (timeout) clearTimeout(timeout);
    timeout = setTimeout(() => {
        performSearch(query.value);
    }, 200);
};

const clearSearch = () => {
    query.value = '';
    if (timeout) clearTimeout(timeout);
    performSearch('');
};

const submit = () => {
    if (timeout) clearTimeout(timeout);
    performSearch(query.value);
};

watch(pageFilters, (newSearch) => {
    if (newSearch !== query.value && document.activeElement?.id !== inputId) {
        query.value = newSearch;
    }
});

onBeforeUnmount(() => {
    if (timeout) clearTimeout(timeout);
});
</script>

<template>
    <form class="relative w-full" role="search" @submit.prevent="submit">
        <label class="sr-only" :for="inputId">Search the catalogue</label>

        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-tabarak-blue md:left-4">
            <Search :class="compact ? 'size-4' : 'size-4 md:size-5'" aria-hidden="true" />
        </span>

        <input
            :id="inputId"
            v-model="query"
            type="search"
            :class="compact
                ? 'h-9 w-full rounded-lg border border-[#E7EAF3] bg-[#F8F9FC] pl-9 pr-22 text-xs text-tabarak-ink placeholder:text-slate-400 focus:border-tabarak-blue focus:bg-white focus:ring-1 focus:ring-tabarak-blue [&::-webkit-search-cancel-button]:hidden [&::-webkit-search-decoration]:hidden'
                : 'h-9.5 w-full rounded-lg border border-[#E7EAF3] bg-[#F8F9FC] pl-9 pr-24 text-xs text-tabarak-ink placeholder:text-slate-400 focus:border-tabarak-blue focus:bg-white focus:ring-1 focus:ring-tabarak-blue md:h-12 md:border-0 md:bg-white md:pl-12 md:pr-28 md:text-sm md:focus:ring-2 md:focus:ring-tabarak-orange [&::-webkit-search-cancel-button]:hidden [&::-webkit-search-decoration]:hidden'"
            placeholder="Search products or SKU"
            autocomplete="off"
            @input="handleInput"
            @keydown.esc="clearSearch"
        />

        <div class="absolute right-1 top-1/2 flex -translate-y-1/2 items-center gap-1 md:right-1.5">
            <button
                v-if="query"
                type="button"
                aria-label="Clear search"
                title="Clear search"
                class="grid size-7 place-items-center rounded-md text-slate-400 transition hover:bg-slate-200/70 hover:text-slate-700 md:size-8"
                @click="clearSearch"
            >
                <X class="size-3.5 md:size-4" />
            </button>

            <button
                class="rounded-md bg-tabarak-blue font-bold text-white transition hover:bg-[#3147C8]"
                :class="compact
                    ? 'min-h-7 px-2.5 text-[11px]'
                    : 'min-h-7.5 px-3 text-xs md:min-h-9 md:px-4 md:text-xs'"
                type="submit"
            >
                Search
            </button>
        </div>
    </form>
</template>
