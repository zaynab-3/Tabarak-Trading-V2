<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Archive, Pencil, Plus, RotateCcw } from '@lucide/vue';
import DataTable from '@/Components/Admin/DataTable.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import StatusBadge from '@/Components/Admin/StatusBadge.vue';
import Pagination from '@/Components/Shared/Pagination.vue';
import ProductFilters from '@/Components/Storefront/ProductFilters.vue';
import { useProductFilters, type ProductFilters as FilterValues } from '@/Composables/useProductFilters';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { Paginated, Product, TaxonomyRef } from '@/types/catalogue';
import { formatDate, productPackLabel } from '@/Utils/format';

const props = defineProps<{ products: Paginated<Product>; filters: FilterValues; categories: TaxonomyRef[]; brands: TaxonomyRef[]; statuses: string[] }>();
const { filters, apply, reset } = useProductFilters(route('admin.products.index'), props.filters);
const archive = (product: Product) => { if (window.confirm(`Archive ${product.name}?`)) router.patch(route('admin.products.archive', product.slug)); };
const restore = (product: Product) => router.patch(route('admin.products.restore', product.slug));
</script>

<template>
    <Head title="Products" /><AdminLayout><PageHeader eyebrow="Catalogue" title="Products" :description="`${products.total} products with server-side search and filtering.`"><Link :href="route('admin.products.create')" class="btn-primary"><Plus class="size-4" /> Add product</Link></PageHeader><ProductFilters :filters="filters" :categories="categories" :brands="brands" admin @apply="apply" @reset="reset" />
        <div class="mt-5"><DataTable label="Products"><thead class="bg-oat-100 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">Image</th><th class="px-4 py-3">Product</th><th class="px-4 py-3">Brand / Category</th><th class="px-4 py-3">Size / Weight</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Featured</th><th class="px-4 py-3">Updated</th><th class="px-4 py-3 text-right">Actions</th></tr></thead><tbody class="divide-y divide-oat-200"><tr v-for="product in products.data" :key="product.id" class="hover:bg-oat-50"><td class="px-4 py-3"><div class="size-12 overflow-hidden rounded bg-oat-100"><img v-if="product.primary_image" :src="product.primary_image.url" :alt="product.name" class="h-full w-full object-cover" /></div></td><td class="px-4 py-3"><p class="font-bold text-forest-900">{{ product.name }}</p><p class="mt-1 text-xs text-slate-400">{{ product.sku || 'No SKU' }}</p></td><td class="px-4 py-3 text-slate-600"><p>{{ product.brand?.name || '—' }}</p><p class="text-xs text-slate-400">{{ product.category?.name || 'Uncategorized' }}</p></td><td class="px-4 py-3 text-slate-600">{{ productPackLabel(product) }}</td><td class="px-4 py-3"><StatusBadge :status="product.status" /></td><td class="px-4 py-3 text-slate-600">{{ product.is_featured ? 'Yes' : 'No' }}</td><td class="px-4 py-3 text-slate-500">{{ formatDate(product.updated_at) }}</td><td class="px-4 py-3"><div class="flex justify-end gap-1"><Link :href="route('admin.products.edit', product.slug)" class="grid size-9 place-items-center rounded border border-oat-300 text-forest-700" aria-label="Edit product"><Pencil class="size-4" /></Link><button v-if="product.status !== 'archived'" class="grid size-9 place-items-center rounded border border-red-200 text-red-600" type="button" aria-label="Archive product" @click="archive(product)"><Archive class="size-4" /></button><button v-else class="grid size-9 place-items-center rounded border border-oat-300 text-forest-700" type="button" aria-label="Restore product" @click="restore(product)"><RotateCcw class="size-4" /></button></div></td></tr></tbody></DataTable></div>
        <div class="mt-8"><Pagination :links="products.links" /></div>
    </AdminLayout>
</template>
