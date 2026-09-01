<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import BrandCard from '@/Components/Storefront/BrandCard.vue';
import Pagination from '@/Components/Shared/Pagination.vue';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { Brand, Paginated } from '@/types/catalogue';
defineProps<{ brands: Paginated<Brand> }>();
</script>
<template><Head title="Brands" /><StorefrontLayout><section class="border-b border-oat-200 bg-white"><div class="page-shell py-10 md:py-14"><p class="eyebrow">Trusted range</p><h1 class="mt-3 font-display text-4xl font-bold text-forest-900 md:text-5xl">Catalogue brands</h1><p class="mt-3 max-w-2xl text-base leading-7 text-slate-600">Explore every active brand and the products available from each line.</p></div></section><section class="page-shell py-10"><div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"><BrandCard v-for="brand in brands.data" :key="brand.id" :brand="brand" /></div><div class="mt-10"><Pagination :links="brands.links" /></div></section></StorefrontLayout></template>
