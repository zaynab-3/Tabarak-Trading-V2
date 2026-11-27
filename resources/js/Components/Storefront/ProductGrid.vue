<script setup lang="ts">
import type { Product } from '@/types/catalogue';
import EmptyState from '@/Components/Shared/EmptyState.vue';
import ProductCard from '@/Components/Storefront/ProductCard.vue';

defineProps<{ products: Product[] }>();
</script>

<template>
    <div v-if="products.length" class="grid grid-cols-2 gap-x-3 gap-y-7 sm:gap-x-4 sm:gap-y-8 md:grid-cols-3 lg:grid-cols-4 lg:gap-x-5 xl:gap-x-6 xl:gap-y-10">
        <ProductCard v-for="product in products" :key="product.id" :product="product" />
    </div>
    <EmptyState v-else title="No products found" description="Try another search, brand, or category." />
</template>
