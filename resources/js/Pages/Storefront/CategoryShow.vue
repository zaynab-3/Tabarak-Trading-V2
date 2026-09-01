<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CollectionHero from '@/Components/Storefront/CollectionHero.vue';
import Pagination from '@/Components/Shared/Pagination.vue';
import ProductGrid from '@/Components/Storefront/ProductGrid.vue';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { Category, Paginated, Product } from '@/types/catalogue';

defineProps<{ category: Category; products: Paginated<Product> }>();
</script>

<template>
    <Head :title="category.name" />
    <StorefrontLayout><CollectionHero eyebrow="Category" :title="category.name" :description="category.description" :count="products.total" /><section class="page-shell py-10"><ProductGrid :products="products.data" /><div class="mt-10"><Pagination :links="products.links" /></div></section></StorefrontLayout>
</template>
