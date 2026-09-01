<script setup lang="ts">
import { RotateCcw } from '@lucide/vue';
import { computed } from 'vue';
import type { ProductFilters } from '@/Composables/useProductFilters';
import type { TaxonomyRef } from '@/types/catalogue';

const props = defineProps<{
    filters: ProductFilters;
    categories: TaxonomyRef[];
    brands: TaxonomyRef[];
    total: number;
}>();
const emit = defineEmits<{ apply: []; reset: [] }>();

const selectedCategory = computed(() => props.categories.find(
    (category) => String(category.id) === String(props.filters.category),
) ?? null);

const selectCategory = (categoryId: number | '') => {
    props.filters.category = categoryId;
    emit('apply');
};
</script>

<template>
    <section class="sticky top-[7.5rem] z-40 border-b border-tabarak-line bg-white shadow-[0_8px_22px_rgba(64,88,225,0.08)] md:top-20">
        <div class="border-b border-tabarak-line">
            <nav class="page-shell flex min-h-[72px] items-stretch gap-1 overflow-x-auto py-2" aria-label="Product categories">
                <button
                    class="min-w-max rounded-md px-4 text-sm font-bold transition"
                    :class="!filters.category ? 'bg-tabarak-orange text-white' : 'text-tabarak-ink hover:bg-tabarak-mist hover:text-tabarak-blue'"
                    type="button"
                    @click="selectCategory('')"
                >
                    All products
                </button>
                <button
                    v-for="category in categories"
                    :key="category.id"
                    class="min-w-max rounded-md px-4 text-sm font-bold transition"
                    :class="String(filters.category) === String(category.id) ? 'bg-tabarak-orange text-white' : 'text-tabarak-ink hover:bg-tabarak-mist hover:text-tabarak-blue'"
                    type="button"
                    @click="selectCategory(category.id)"
                >
                    {{ category.name }}
                </button>
            </nav>
        </div>

        <div class="page-shell grid gap-4 py-4 sm:grid-cols-[1fr_180px_160px_auto] sm:items-end">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-tabarak-orange">Catalogue</p>
                <div class="mt-1 flex items-baseline gap-3">
                    <h1 class="font-display text-3xl font-bold text-tabarak-blue md:text-4xl">{{ selectedCategory?.name || 'All products' }}</h1>
                    <span class="text-sm font-semibold text-slate-500">{{ total }} products</span>
                </div>
            </div>
            <label class="block">
                <span class="mb-1.5 block text-xs font-bold text-slate-600">Brand</span>
                <select v-model="filters.brand" class="min-h-11 w-full rounded-md border-tabarak-line bg-white text-sm text-tabarak-ink focus:border-tabarak-blue focus:ring-tabarak-blue" @change="emit('apply')">
                    <option value="">All brands</option>
                    <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                </select>
            </label>
            <label class="block">
                <span class="mb-1.5 block text-xs font-bold text-slate-600">Sort</span>
                <select v-model="filters.sort" class="min-h-11 w-full rounded-md border-tabarak-line bg-white text-sm text-tabarak-ink focus:border-tabarak-blue focus:ring-tabarak-blue" @change="emit('apply')">
                    <option value="newest">Newest</option>
                    <option value="name-asc">Name A–Z</option>
                    <option value="name-desc">Name Z–A</option>
                </select>
            </label>
            <button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md border border-tabarak-line bg-white px-4 text-sm font-bold text-tabarak-blue transition hover:border-tabarak-orange hover:text-tabarak-orange" type="button" @click="emit('reset')">
                <RotateCcw class="size-4" /> Reset
            </button>
        </div>
    </section>
</template>
