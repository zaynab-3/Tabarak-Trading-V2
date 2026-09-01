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
        <section class="page-shell py-6 md:py-8">
            <div class="mb-5 flex items-center justify-between text-sm text-slate-500">
                <span>Browse the catalogue</span>
                <span v-if="products.from">Viewing {{ products.from }}–{{ products.to }} of {{ products.total }}</span>
            </div>
            <ProductGrid :products="products.data" />
            <div class="mt-10"><Pagination :links="products.links" storefront /></div>
        </section>
    </StorefrontLayout>
</template>
