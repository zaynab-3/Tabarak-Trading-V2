<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Search } from '@lucide/vue';
import { ref } from 'vue';

const props = defineProps<{ initial?: string }>();
const query = ref(props.initial ?? '');

const submit = () => router.get(route('search'), { search: query.value }, { preserveState: false });
</script>

<template>
    <form class="relative w-full" role="search" @submit.prevent="submit">
        <label class="sr-only" for="catalogue-search">Search the catalogue</label>
        <Search class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-slate-400" aria-hidden="true" />
        <input id="catalogue-search" v-model="query" type="search" class="field-input h-12 w-full border-oat-300 bg-oat-50 pl-12 pr-24" placeholder="Search products or SKU" />
        <button class="absolute right-1.5 top-1.5 min-h-9 rounded bg-forest-800 px-4 text-xs font-bold text-white" type="submit">Search</button>
    </form>
</template>
