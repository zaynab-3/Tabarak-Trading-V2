<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Pencil, Power } from '@lucide/vue';
import { ref } from 'vue';
import CategoryForm from '@/Components/Admin/CategoryForm.vue';
import DataTable from '@/Components/Admin/DataTable.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import Pagination from '@/Components/Shared/Pagination.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { Category, MediaItem, Paginated, TaxonomyRef } from '@/types/catalogue';

defineProps<{ categories: Paginated<Category>; parents: TaxonomyRef[]; media: MediaItem[] }>();
const selected = ref<Category | null>(null);
const toggle = (category: Category) => router.patch(route('admin.categories.toggle', category.slug), {}, { preserveScroll: true });
</script>

<template>
    <Head title="Categories" /><AdminLayout><PageHeader eyebrow="Catalogue structure" title="Categories" description="Organize the range into clear, sortable storefront aisles." />
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]"><div><DataTable label="Categories"><thead class="bg-oat-100 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">Category</th><th class="px-4 py-3">Parent</th><th class="px-4 py-3">Products</th><th class="px-4 py-3">Order</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Actions</th></tr></thead><tbody class="divide-y divide-oat-200"><tr v-for="category in categories.data" :key="category.id"><td class="px-4 py-3 font-bold text-forest-900">{{ category.name }}</td><td class="px-4 py-3 text-slate-500">{{ category.parent?.name || '—' }}</td><td class="px-4 py-3 text-slate-500">{{ category.products_count }}</td><td class="px-4 py-3 text-slate-500">{{ category.sort_order }}</td><td class="px-4 py-3"><span class="text-xs font-bold" :class="category.is_active ? 'text-emerald-700' : 'text-red-600'">{{ category.is_active ? 'Active' : 'Inactive' }}</span></td><td class="px-4 py-3"><div class="flex justify-end gap-1"><button class="grid size-9 place-items-center rounded border border-oat-300 text-forest-700" type="button" aria-label="Edit category" @click="selected = category"><Pencil class="size-4" /></button><button class="grid size-9 place-items-center rounded border border-oat-300 text-slate-500" type="button" aria-label="Toggle category status" @click="toggle(category)"><Power class="size-4" /></button></div></td></tr></tbody></DataTable><div class="mt-7"><Pagination :links="categories.links" /></div></div>
            <aside class="surface h-fit p-5 xl:sticky xl:top-6"><div class="mb-4 flex items-center justify-between"><h2 class="font-display text-xl font-bold text-forest-900">{{ selected ? 'Edit category' : 'New category' }}</h2><button v-if="selected" class="text-xs font-bold text-slate-500" type="button" @click="selected = null">New instead</button></div><CategoryForm :key="selected?.id || 'new'" :category="selected" :parents="parents" :media="media" @saved="selected = null" /></aside>
        </div>
    </AdminLayout>
</template>
