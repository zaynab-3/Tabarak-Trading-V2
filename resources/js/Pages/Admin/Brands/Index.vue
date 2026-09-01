<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Pencil, Plus, Power } from '@lucide/vue';
import { ref } from 'vue';
import BrandForm from '@/Components/Admin/BrandForm.vue';
import DataTable from '@/Components/Admin/DataTable.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import Pagination from '@/Components/Shared/Pagination.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { Brand, MediaItem, Paginated } from '@/types/catalogue';

defineProps<{ brands: Paginated<Brand>; media: MediaItem[] }>();
const selected = ref<Brand | null>(null);
const toggle = (brand: Brand) => router.patch(route('admin.brands.toggle', brand.slug), {}, { preserveScroll: true });
</script>

<template>
    <Head title="Brands" />
    <AdminLayout>
        <PageHeader eyebrow="Catalogue structure" title="Brands" description="Manage active brand lines, descriptions, and logos from one view." />
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
            <div class="min-w-0">
                <DataTable label="Brands">
                    <thead class="text-xs uppercase tracking-wider text-slate-500">
                        <tr><th class="px-4 py-3">Brand</th><th class="px-4 py-3">Products</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Actions</th></tr>
                    </thead>
                    <tbody class="divide-y divide-tabarak-line">
                        <tr v-for="brand in brands.data" :key="brand.id">
                            <td class="px-4 py-3"><p class="font-bold text-tabarak-ink">{{ brand.name }}</p><p class="mt-1 max-w-md truncate text-xs text-slate-500">{{ brand.description || 'No description' }}</p></td>
                            <td class="px-4 py-3 text-slate-500">{{ brand.products_count }}</td>
                            <td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold" :class="brand.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600'">{{ brand.is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="px-4 py-3"><div class="flex justify-end gap-1.5"><button class="admin-table-action" type="button" aria-label="Edit brand" @click="selected = brand"><Pencil class="size-4" /></button><button class="admin-table-action" type="button" aria-label="Toggle brand status" @click="toggle(brand)"><Power class="size-4" /></button></div></td>
                        </tr>
                    </tbody>
                </DataTable>
                <div class="mt-7"><Pagination :links="brands.links" /></div>
            </div>

            <aside class="surface h-fit p-5 xl:sticky xl:top-24">
                <div class="mb-5 flex items-center justify-between border-b border-tabarak-line pb-4">
                    <div><p class="eyebrow">{{ selected ? 'Editing' : 'Create' }}</p><h2 class="mt-1 font-display text-xl font-bold text-tabarak-ink">{{ selected ? selected.name : 'New brand' }}</h2></div>
                    <button v-if="selected" class="admin-icon-button" type="button" aria-label="Create a new brand instead" @click="selected = null"><Plus class="size-4" /></button>
                </div>
                <BrandForm :key="selected?.id || 'new'" :brand="selected" :media="media" @saved="selected = null" />
            </aside>
        </div>
    </AdminLayout>
</template>
