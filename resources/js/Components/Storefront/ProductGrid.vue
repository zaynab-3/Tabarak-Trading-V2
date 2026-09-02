<script setup lang="ts">
import type { Product } from '@/types/catalogue';
import EmptyState from '@/Components/Shared/EmptyState.vue';
import ProductCard from '@/Components/Storefront/ProductCard.vue';

defineProps<{ products: Product[] }>();
</script>

<template>
    <div v-if="products.length" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:gap-5">
        <ProductCard v-for="product in products" :key="product.id" :product="product" />
    </div>
    <EmptyState v-else title="No products found" description="Try changing the search or filters to see more of the catalogue." />
</template>
