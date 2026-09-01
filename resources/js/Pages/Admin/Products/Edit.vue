<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import ProductForm from '@/Components/Admin/ProductForm.vue';
import ProductImageManager from '@/Components/Admin/ProductImageManager.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { Product, TaxonomyRef } from '@/types/catalogue';
defineProps<{ product: Product; categories: TaxonomyRef[]; brands: TaxonomyRef[]; statuses: string[] }>();
</script>
<template><Head :title="`Edit ${product.name}`" /><AdminLayout><PageHeader eyebrow="Products" :title="`Edit ${product.name}`" description="Update catalogue information and manage the product gallery." /><ProductForm :product="product" :categories="categories" :brands="brands" :statuses="statuses" /><ProductImageManager :product="product" /></AdminLayout></template>
