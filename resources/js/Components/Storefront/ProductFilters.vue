<script setup lang="ts">
import { Filter, RotateCcw, Search } from '@lucide/vue';
import type { TaxonomyRef } from '@/types/catalogue';
import type { ProductFilters } from '@/Composables/useProductFilters';

defineProps<{ filters: ProductFilters; categories: TaxonomyRef[]; brands: TaxonomyRef[]; admin?: boolean }>();
const emit = defineEmits<{ apply: []; reset: [] }>();
</script>

<template>
    <form class="surface grid gap-3 p-4 md:grid-cols-2 xl:flex xl:items-end" @submit.prevent="emit('apply')">
        <label class="block min-w-52 flex-1"><span class="field-label">Search</span><span class="relative block"><Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" /><input v-model="filters.search" class="field-input pl-10" placeholder="Name or SKU" /></span></label>
        <label class="block md:min-w-44"><span class="field-label">Category</span><select v-model="filters.category" class="field-input"><option value="">All categories</option><option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option></select></label>
        <label class="block md:min-w-44"><span class="field-label">Brand</span><select v-model="filters.brand" class="field-input"><option value="">All brands</option><option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option></select></label>
        <label v-if="admin" class="block md:min-w-40"><span class="field-label">Status</span><select v-model="filters.status" class="field-input"><option value="">All statuses</option><option value="published">Published</option><option value="draft">Draft</option><option value="archived">Archived</option></select></label>
        <label v-else class="block md:min-w-40"><span class="field-label">Sort</span><select v-model="filters.sort" class="field-input"><option value="newest">Newest</option><option value="name-asc">Name A–Z</option><option value="name-desc">Name Z–A</option></select></label>
        <div class="flex gap-2 md:col-span-2 xl:col-span-1"><button class="btn-primary flex-1 xl:flex-none" type="submit"><Filter class="size-4" /> Apply</button><button class="btn-secondary px-3" type="button" aria-label="Reset filters" @click="emit('reset')"><RotateCcw class="size-4" /></button></div>
    </form>
</template>
