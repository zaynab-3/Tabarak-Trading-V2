<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Pagination from '@/Components/Shared/Pagination.vue';
import ProductFilters from '@/Components/Storefront/ProductFilters.vue';
import ProductGrid from '@/Components/Storefront/ProductGrid.vue';
import { useProductFilters, type ProductFilters as FilterValues } from '@/Composables/useProductFilters';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { Paginated, Product, TaxonomyRef } from '@/types/catalogue';

const props = defineProps<{ products: Paginated<Product>; filters: FilterValues; categories: TaxonomyRef[]; brands: TaxonomyRef[] }>();
const { filters, apply, reset } = useProductFilters(route('shop'), props.filters);
</script>

<template>
    <Head title="Shop the catalogue" />
    <StorefrontLayout><section class="border-b border-oat-200 bg-white"><div class="page-shell py-10 md:py-14"><p class="eyebrow">Complete range</p><h1 class="mt-3 font-display text-4xl font-bold text-forest-900 md:text-5xl">Wholesale catalogue</h1><p class="mt-3 max-w-2xl text-base leading-7 text-slate-600">Search by product or SKU, then narrow the range without loading the full catalogue into your browser.</p></div></section><section class="page-shell py-7 md:py-10"><ProductFilters :filters="filters" :categories="categories" :brands="brands" @apply="apply" @reset="reset" /><div class="my-6 flex items-center justify-between text-sm text-slate-500"><span>{{ products.total }} products</span><span v-if="products.from">Showing {{ products.from }}–{{ products.to }}</span></div><ProductGrid :products="products.data" /><div class="mt-10"><Pagination :links="products.links" /></div></section></StorefrontLayout>
</template>
