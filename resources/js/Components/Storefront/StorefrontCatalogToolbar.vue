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
    props.filters.brand = '';
    emit('apply');
};
</script>

<template>
    <section class="sticky top-[7rem] z-40 border-b border-tabarak-line bg-white shadow-[0_8px_22px_rgba(64,88,225,0.08)] md:top-16">
        <nav class="page-shell flex min-h-12 items-center gap-1 overflow-x-auto border-b border-tabarak-line py-1" aria-label="Product categories">
            <button
                class="min-h-10 min-w-max rounded-md px-3 text-[13px] font-bold transition"
                :class="!filters.category ? 'bg-tabarak-orange text-white' : 'text-tabarak-ink hover:bg-tabarak-mist hover:text-tabarak-blue'"
                type="button"
                @click="selectCategory('')"
            >
                All products
            </button>
            <button
                v-for="category in categories"
                :key="category.id"
                class="min-h-10 min-w-max rounded-md px-3 text-[13px] font-bold transition"
                :class="String(filters.category) === String(category.id) ? 'bg-tabarak-orange text-white' : 'text-tabarak-ink hover:bg-tabarak-mist hover:text-tabarak-blue'"
                type="button"
                @click="selectCategory(category.id)"
            >
                {{ category.name }}
            </button>
        </nav>

        <div class="page-shell flex min-h-14 flex-wrap items-center gap-2 py-2 sm:flex-nowrap">
            <div class="flex w-full min-w-0 items-baseline gap-2 sm:mr-auto sm:w-auto">
                <h1 class="truncate font-display text-xl font-bold text-tabarak-blue md:text-2xl">{{ selectedCategory?.name || 'All products' }}</h1>
                <span class="shrink-0 text-xs font-semibold text-slate-500">{{ total }} products</span>
            </div>

            <div class="grid w-full grid-cols-[minmax(0,1fr)_minmax(0,1fr)_44px] gap-2 sm:w-auto sm:grid-cols-[160px_140px_44px]">
                <label class="block">
                    <span class="sr-only">Filter by brand</span>
                    <select v-model="filters.brand" class="min-h-10 w-full rounded-md border-tabarak-line bg-white py-2 text-sm text-tabarak-ink focus:border-tabarak-blue focus:ring-tabarak-blue" aria-label="Filter by brand" @change="emit('apply')">
                        <option value="">{{ filters.category ? 'All brands in category' : 'All brands' }}</option>
                        <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                    </select>
                </label>
                <label class="block">
                    <span class="sr-only">Sort products</span>
                    <select v-model="filters.sort" class="min-h-10 w-full rounded-md border-tabarak-line bg-white py-2 text-sm text-tabarak-ink focus:border-tabarak-blue focus:ring-tabarak-blue" aria-label="Sort products" @change="emit('apply')">
                        <option value="newest">Newest</option>
                        <option value="name-asc">Name A–Z</option>
                        <option value="name-desc">Name Z–A</option>
                    </select>
                </label>
                <button class="grid size-11 place-items-center rounded-md border border-tabarak-line bg-white text-tabarak-blue transition hover:border-tabarak-orange hover:text-tabarak-orange" type="button" aria-label="Reset catalogue filters" title="Reset filters" @click="emit('reset')">
                    <RotateCcw class="size-4" />
                </button>
            </div>
        </div>
    </section>
</template>
