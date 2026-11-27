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

        <input
            :id="inputId"
            v-model="query"
            type="search"
            :class="compact
                ? 'h-9 w-full rounded-lg border border-[#E7EAF3] bg-[#F8F9FC] pl-3 pr-16 text-xs text-tabarak-ink placeholder:text-slate-400 focus:border-tabarak-blue focus:bg-white focus:ring-1 focus:ring-tabarak-blue [&::-webkit-search-cancel-button]:hidden [&::-webkit-search-decoration]:hidden'
                : 'h-9.5 w-full rounded-lg border border-[#E7EAF3] bg-[#F8F9FC] pl-3 pr-16 text-xs text-tabarak-ink placeholder:text-slate-400 focus:border-tabarak-blue focus:bg-white focus:ring-1 focus:ring-tabarak-blue md:h-12 md:border-0 md:bg-white md:pl-5 md:pr-20 md:text-sm md:focus:ring-2 md:focus:ring-tabarak-orange [&::-webkit-search-cancel-button]:hidden [&::-webkit-search-decoration]:hidden'"
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
                class="grid size-7 place-items-center rounded-md text-tabarak-blue transition hover:bg-tabarak-mist md:size-9"
                type="submit"
                aria-label="Search"
                title="Search"
            >
                <Search :class="compact ? 'size-4' : 'size-4 md:size-5'" aria-hidden="true" />
            </button>
        </div>
    </form>
</template>
