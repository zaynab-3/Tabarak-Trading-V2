<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CategoryCard from '@/Components/Storefront/CategoryCard.vue';
import Pagination from '@/Components/Shared/Pagination.vue';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { Category, Paginated } from '@/types/catalogue';
defineProps<{ categories: Paginated<Category> }>();
</script>
<template><Head title="Categories" /><StorefrontLayout><section class="border-b border-oat-200 bg-white"><div class="page-shell py-10 md:py-14"><p class="eyebrow">Browse the range</p><h1 class="mt-3 font-display text-4xl font-bold text-forest-900 md:text-5xl">Product categories</h1><p class="mt-3 max-w-2xl text-base leading-7 text-slate-600">A clear aisle-by-aisle view of the wholesale catalogue.</p></div></section><section class="page-shell py-10"><div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4"><CategoryCard v-for="category in categories.data" :key="category.id" :category="category" /></div><div class="mt-10"><Pagination :links="categories.links" /></div></section></StorefrontLayout></template>
