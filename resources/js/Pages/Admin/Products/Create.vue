<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import ProductForm from '@/Components/Admin/ProductForm.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { TaxonomyRef } from '@/types/catalogue';
defineProps<{ categories: TaxonomyRef[]; brands: TaxonomyRef[]; statuses: string[] }>();
</script>
<template><Head title="Create product" /><AdminLayout><PageHeader eyebrow="Products" title="Create product" description="Add core catalogue details first, then attach product imagery." /><ProductForm :categories="categories" :brands="brands" :statuses="statuses" /></AdminLayout></template>
