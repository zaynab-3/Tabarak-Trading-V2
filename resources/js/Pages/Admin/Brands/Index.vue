<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Pencil, Power } from '@lucide/vue';
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
    <Head title="Brands" /><AdminLayout><PageHeader eyebrow="Catalogue structure" title="Brands" description="Maintain active brand lines, descriptions and logos." />
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]"><div><DataTable label="Brands"><thead class="bg-oat-100 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">Brand</th><th class="px-4 py-3">Products</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Actions</th></tr></thead><tbody class="divide-y divide-oat-200"><tr v-for="brand in brands.data" :key="brand.id"><td class="px-4 py-3"><p class="font-bold text-forest-900">{{ brand.name }}</p><p class="mt-1 max-w-md truncate text-xs text-slate-500">{{ brand.description }}</p></td><td class="px-4 py-3 text-slate-500">{{ brand.products_count }}</td><td class="px-4 py-3"><span class="text-xs font-bold" :class="brand.is_active ? 'text-emerald-700' : 'text-red-600'">{{ brand.is_active ? 'Active' : 'Inactive' }}</span></td><td class="px-4 py-3"><div class="flex justify-end gap-1"><button class="grid size-9 place-items-center rounded border border-oat-300 text-forest-700" type="button" aria-label="Edit brand" @click="selected = brand"><Pencil class="size-4" /></button><button class="grid size-9 place-items-center rounded border border-oat-300 text-slate-500" type="button" aria-label="Toggle brand status" @click="toggle(brand)"><Power class="size-4" /></button></div></td></tr></tbody></DataTable><div class="mt-7"><Pagination :links="brands.links" /></div></div>
            <aside class="surface h-fit p-5 xl:sticky xl:top-6"><div class="mb-4 flex items-center justify-between"><h2 class="font-display text-xl font-bold text-forest-900">{{ selected ? 'Edit brand' : 'New brand' }}</h2><button v-if="selected" class="text-xs font-bold text-slate-500" type="button" @click="selected = null">New instead</button></div><BrandForm :key="selected?.id || 'new'" :brand="selected" :media="media" @saved="selected = null" /></aside>
        </div>
    </AdminLayout>
</template>
