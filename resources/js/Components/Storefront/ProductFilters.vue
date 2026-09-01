<script setup lang="ts">
import { Filter, RotateCcw, Search } from '@lucide/vue';
import { computed } from 'vue';
import SelectMenu, { type SelectOption } from '@/Components/Shared/SelectMenu.vue';
import type { TaxonomyRef } from '@/types/catalogue';
import type { ProductFilters } from '@/Composables/useProductFilters';

const props = defineProps<{ filters: ProductFilters; categories: TaxonomyRef[]; brands: TaxonomyRef[]; admin?: boolean }>();
const emit = defineEmits<{ apply: []; reset: [] }>();
const categoryOptions = computed<SelectOption[]>(() => [{ value: '', label: 'All categories' }, ...props.categories.map((item) => ({ value: item.id, label: item.name }))]);
const brandOptions = computed<SelectOption[]>(() => [{ value: '', label: 'All brands' }, ...props.brands.map((item) => ({ value: item.id, label: item.name }))]);
const statusOptions: SelectOption[] = [
    { value: '', label: 'All statuses' },
    { value: 'published', label: 'Published' },
    { value: 'draft', label: 'Draft' },
    { value: 'archived', label: 'Archived' },
];
const sortOptions: SelectOption[] = [
    { value: 'newest', label: 'Newest' },
    { value: 'name-asc', label: 'Name A–Z' },
    { value: 'name-desc', label: 'Name Z–A' },
];
</script>

<template>
    <form class="surface grid gap-3 p-4 md:grid-cols-2 xl:flex xl:items-end" @submit.prevent="emit('apply')">
        <label class="block min-w-52 flex-1"><span class="field-label">Search</span><span class="relative block"><Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" /><input v-model="filters.search" class="field-input pl-10" placeholder="Name or SKU" /></span></label>
        <label class="block md:min-w-44"><span class="field-label">Category</span><SelectMenu v-model="filters.category" :options="categoryOptions" aria-label="Filter by category" /></label>
        <label class="block md:min-w-44"><span class="field-label">Brand</span><SelectMenu v-model="filters.brand" :options="brandOptions" aria-label="Filter by brand" /></label>
        <label v-if="admin" class="block md:min-w-40"><span class="field-label">Status</span><SelectMenu v-model="filters.status" :options="statusOptions" aria-label="Filter by status" /></label>
        <label v-else class="block md:min-w-40"><span class="field-label">Sort</span><SelectMenu v-model="filters.sort" :options="sortOptions" aria-label="Sort products" /></label>
        <div class="flex gap-2 md:col-span-2 xl:col-span-1"><button class="btn-primary flex-1 xl:flex-none" type="submit"><Filter class="size-4" /> Apply</button><button class="btn-secondary px-3" type="button" aria-label="Reset filters" @click="emit('reset')"><RotateCcw class="size-4" /></button></div>
    </form>
</template>
