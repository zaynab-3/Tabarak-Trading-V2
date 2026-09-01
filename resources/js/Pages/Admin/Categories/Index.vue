<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Pencil, Plus, Power } from '@lucide/vue';
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
    <Head title="Categories" />
    <AdminLayout>
        <PageHeader eyebrow="Catalogue structure" title="Categories" description="Keep the storefront organized with one clear, manageable category list." />
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
            <div class="min-w-0">
                <DataTable label="Categories">
                    <thead class="text-xs uppercase tracking-wider text-slate-500">
                        <tr><th class="px-4 py-3">Category</th><th class="px-4 py-3">Parent</th><th class="px-4 py-3">Products</th><th class="px-4 py-3">Order</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Actions</th></tr>
                    </thead>
                    <tbody class="divide-y divide-tabarak-line">
                        <tr v-for="category in categories.data" :key="category.id">
                            <td class="px-4 py-3 font-bold text-tabarak-ink">{{ category.name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ category.parent?.name || 'None' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ category.products_count }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ category.sort_order }}</td>
                            <td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold" :class="category.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600'">{{ category.is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="px-4 py-3"><div class="flex justify-end gap-1.5"><button class="admin-table-action" type="button" aria-label="Edit category" @click="selected = category"><Pencil class="size-4" /></button><button class="admin-table-action" type="button" aria-label="Toggle category status" @click="toggle(category)"><Power class="size-4" /></button></div></td>
                        </tr>
                    </tbody>
                </DataTable>
                <div class="mt-7"><Pagination :links="categories.links" /></div>
            </div>

            <aside class="surface h-fit p-5 xl:sticky xl:top-24">
                <div class="mb-5 flex items-center justify-between border-b border-tabarak-line pb-4">
                    <div><p class="eyebrow">{{ selected ? 'Editing' : 'Create' }}</p><h2 class="mt-1 font-display text-xl font-bold text-tabarak-ink">{{ selected ? selected.name : 'New category' }}</h2></div>
                    <button v-if="selected" class="admin-icon-button" type="button" aria-label="Create a new category instead" @click="selected = null"><Plus class="size-4" /></button>
                </div>
                <CategoryForm :key="selected?.id || 'new'" :category="selected" :parents="parents" :media="media" @saved="selected = null" />
            </aside>
        </div>
    </AdminLayout>
</template>
