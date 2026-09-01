<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { Search } from '@lucide/vue';
import { ref } from 'vue';

const props = defineProps<{ initial?: string }>();
const page = usePage();
const pageFilters = page.props.filters as { search?: string } | undefined;
const query = ref(props.initial ?? pageFilters?.search ?? '');

const submit = () => router.get(route('search'), { search: query.value }, { preserveState: false });
</script>

<template>
    <form class="relative w-full" role="search" @submit.prevent="submit">
        <label class="sr-only" for="catalogue-search">Search the catalogue</label>
        <Search class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-tabarak-blue" aria-hidden="true" />
        <input id="catalogue-search" v-model="query" type="search" class="h-12 w-full rounded-md border-0 bg-white pl-12 pr-24 text-sm text-tabarak-ink shadow-none placeholder:text-slate-400 focus:ring-2 focus:ring-tabarak-orange" placeholder="Search products or SKU" />
        <button class="absolute right-1.5 top-1.5 min-h-9 rounded bg-tabarak-blue px-4 text-xs font-bold text-white transition hover:bg-[#3147C8]" type="submit">Search</button>
    </form>
</template>
