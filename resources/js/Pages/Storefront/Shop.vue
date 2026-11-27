<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Pagination from '@/Components/Shared/Pagination.vue';
import ProductGrid from '@/Components/Storefront/ProductGrid.vue';
import StorefrontCatalogToolbar from '@/Components/Storefront/StorefrontCatalogToolbar.vue';
import { useProductFilters, type ProductFilters as FilterValues } from '@/Composables/useProductFilters';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { Paginated, Product, TaxonomyRef } from '@/types/catalogue';

const props = defineProps<{ products: Paginated<Product>; filters: FilterValues; categories: TaxonomyRef[]; brands: TaxonomyRef[] }>();
const { filters, apply, reset } = useProductFilters(route('shop'), props.filters);
</script>

<template>
    <Head title="Shop the catalogue" />
    <StorefrontLayout>
        <StorefrontCatalogToolbar :filters="filters" :categories="categories" :brands="brands" :total="products.total" @apply="apply" @reset="reset" />
        <section class="page-shell pb-12 pt-6 sm:pt-8 lg:pb-16">
            <p v-if="products.from" class="mb-5 text-xs font-semibold text-slate-400 sm:mb-7 sm:text-sm">
                Showing {{ products.from }}–{{ products.to }} of {{ products.total }} products
            </p>
            <ProductGrid :products="products.data" />
            <div class="mt-12 sm:mt-14"><Pagination :links="products.links" storefront /></div>
        </section>
    </StorefrontLayout>
</template>
