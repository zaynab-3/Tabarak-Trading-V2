<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Boxes, Building2, FolderTree, Images, SendToBack, Sparkles } from '@lucide/vue';
import DataTable from '@/Components/Admin/DataTable.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import StatCard from '@/Components/Admin/StatCard.vue';
import StatusBadge from '@/Components/Admin/StatusBadge.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { ImportBatch, Product } from '@/types/catalogue';
import { formatDate } from '@/Utils/format';

defineProps<{ stats: { products: number; published: number; categories: number; brands: number; media: number; importsAwaitingReview: number }; recentProducts: Product[]; recentImports: ImportBatch[] }>();
</script>

<template>
    <Head title="Admin dashboard" /><AdminLayout><PageHeader eyebrow="Overview" title="Catalogue dashboard" description="A quick operational view of products, media and import work." ><Link :href="route('admin.products.create')" class="btn-primary">Add product</Link></PageHeader>
        <section class="grid gap-3 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6"><StatCard label="Products" :value="stats.products" :icon="Boxes" /><StatCard label="Published" :value="stats.published" :icon="SendToBack" tone="gold" /><StatCard label="Categories" :value="stats.categories" :icon="FolderTree" /><StatCard label="Brands" :value="stats.brands" :icon="Building2" /><StatCard label="Media" :value="stats.media" :icon="Images" /><StatCard label="Review imports" :value="stats.importsAwaitingReview" :icon="Sparkles" tone="gold" /></section>
        <div class="mt-6 grid gap-6 xl:grid-cols-[1.4fr_0.8fr]"><section><div class="mb-3 flex items-center justify-between"><h2 class="font-display text-xl font-bold text-forest-900">Recently updated products</h2><Link :href="route('admin.products.index')" class="text-sm font-bold text-forest-700">View all</Link></div><DataTable label="Recent products"><thead class="bg-oat-100 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">Product</th><th class="px-4 py-3">Category</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Updated</th></tr></thead><tbody class="divide-y divide-oat-200"><tr v-for="product in recentProducts" :key="product.id"><td class="px-4 py-3 font-semibold text-forest-900">{{ product.name }}</td><td class="px-4 py-3 text-slate-500">{{ product.category?.name || '—' }}</td><td class="px-4 py-3"><StatusBadge :status="product.status" /></td><td class="px-4 py-3 text-slate-500">{{ formatDate(product.updated_at) }}</td></tr></tbody></DataTable></section>
            <section><div class="mb-3 flex items-center justify-between"><h2 class="font-display text-xl font-bold text-forest-900">Recent imports</h2><Link :href="route('admin.imports.index')" class="text-sm font-bold text-forest-700">View all</Link></div><div class="surface divide-y divide-oat-200"><Link v-for="batch in recentImports" :key="batch.id" :href="route('admin.imports.show', batch.id)" class="flex items-center justify-between gap-3 p-4 hover:bg-oat-50"><div><p class="text-sm font-bold text-forest-900">{{ batch.name }}</p><p class="mt-1 text-xs text-slate-500">{{ batch.total_items }} images · {{ formatDate(batch.created_at) }}</p></div><StatusBadge :status="batch.status" /></Link><p v-if="!recentImports.length" class="p-6 text-center text-sm text-slate-500">No imports yet.</p></div></section></div>
    </AdminLayout>
</template>
