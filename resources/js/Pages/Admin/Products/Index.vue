<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Archive, Download, Image as ImageIcon, Pencil, Plus, RotateCcw, Trash2, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import ConfirmDialog from '@/Components/Shared/ConfirmDialog.vue';
import DataTable from '@/Components/Admin/DataTable.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import StatusBadge from '@/Components/Admin/StatusBadge.vue';
import Pagination from '@/Components/Shared/Pagination.vue';
import ProductFilters from '@/Components/Storefront/ProductFilters.vue';
import { useProductFilters, type ProductFilters as FilterValues } from '@/Composables/useProductFilters';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { Paginated, Product, TaxonomyRef } from '@/types/catalogue';
import { formatDate, formatMoney, productPackLabel } from '@/Utils/format';

const props = defineProps<{
    products: Paginated<Product>;
    filters: FilterValues;
    categories: TaxonomyRef[];
    brands: TaxonomyRef[];
    statuses: string[];
    exportColumns: { key: string; label: string }[];
}>();

const { filters, apply, reset } = useProductFilters(route('admin.products.index'), props.filters);
const exportDialog = ref<HTMLDialogElement | null>(null);
const selectedColumns = ref<string[]>(['name', 'unit_price']);
const exportScope = ref<'all' | 'filtered'>('all');
const allColumnsSelected = computed(() => props.exportColumns.every(column => selectedColumns.value.includes(column.key)));
const toggleAllColumns = (event: Event) => {
    selectedColumns.value = (event.target as HTMLInputElement).checked ? props.exportColumns.map(column => column.key) : [];
};
const openExport = () => exportDialog.value?.showModal();
const closeExport = () => exportDialog.value?.close();
const pendingAction = ref<{ type: 'archive' | 'delete'; product: Product } | null>(null);
const actionProcessing = ref(false);
const dialogTitle = computed(() => pendingAction.value?.type === 'delete' ? 'Delete product permanently?' : 'Archive this product?');
const dialogDescription = computed(() => pendingAction.value?.type === 'delete'
    ? `${pendingAction.value.product.name} will be permanently removed, including its catalogue relationships. This cannot be undone.`
    : `${pendingAction.value?.product.name} will be removed from the public catalogue and kept in the admin archive.`);
const confirmAction = () => {
    if (!pendingAction.value) return;
    actionProcessing.value = true;
    const { type, product } = pendingAction.value;
    const options = { preserveScroll: true, onFinish: () => { actionProcessing.value = false; pendingAction.value = null; } };
    if (type === 'delete') router.delete(route('admin.products.destroy', product.slug), options);
    else router.patch(route('admin.products.archive', product.slug), {}, options);
};
const restore = (product: Product) => router.patch(route('admin.products.restore', product.slug));
</script>

<template>
    <Head title="Products" />
    <AdminLayout>
        <PageHeader eyebrow="Catalogue" title="Products" :description="`${products.total} products with fast search and focused filters.`">
            <button type="button" class="btn-secondary" @click="openExport"><Download class="size-4" /> Export Excel</button>
            <Link :href="route('admin.products.create')" class="btn-primary"><Plus class="size-4" /> Add product</Link>
        </PageHeader>

        <ProductFilters :filters="filters" :categories="categories" :brands="brands" admin @apply="apply" @reset="reset" />

        <div class="mt-5">
            <DataTable label="Products">
                <thead class="text-xs uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Product</th>
                        <th class="px-4 py-3">Classification</th>
                        <th class="hidden px-4 py-3 xl:table-cell">Pack details</th>
                        <th class="px-4 py-3">USD price</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="hidden px-4 py-3 2xl:table-cell">Updated</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-tabarak-line">
                    <tr v-for="product in products.data" :key="product.id">
                        <td class="px-4 py-3">
                            <div class="flex min-w-52 items-center gap-3">
                                <div class="grid size-14 shrink-0 place-items-center overflow-hidden rounded-md bg-tabarak-mist p-1">
                                    <img v-if="product.primary_image" :src="product.primary_image.url" :alt="product.name" class="h-full w-full object-contain" />
                                    <ImageIcon v-else class="size-5 text-slate-300" />
                                </div>
                                <div class="min-w-0">
                                    <p class="line-clamp-2 font-bold leading-5 text-tabarak-ink">{{ product.name }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-400">
                                        <span>{{ product.sku || 'No SKU' }}</span>
                                        <span v-if="product.is_featured" class="rounded bg-[#FFF0E8] px-1.5 py-0.5 font-bold text-tabarak-orange">Featured</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            <p class="font-semibold text-tabarak-ink">{{ product.brand?.name || 'No brand' }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ product.category?.name || 'Uncategorized' }}</p>
                        </td>
                        <td class="hidden px-4 py-3 text-slate-600 xl:table-cell">{{ productPackLabel(product) }}</td>
                        <td class="px-4 py-3"><p class="font-bold text-tabarak-ink">{{ product.unit_price ? formatMoney(product.unit_price) : 'Not priced' }}</p><p class="mt-1 text-xs" :class="product.is_available ? 'text-slate-400' : 'font-bold text-red-600'">{{ product.allows_open_quantity ? 'Open quantity' : product.stock_quantity === null ? 'Stock not tracked' : `${product.stock_quantity} in stock` }}</p></td>
                        <td class="px-4 py-3"><StatusBadge :status="product.status" /></td>
                        <td class="hidden px-4 py-3 text-slate-500 2xl:table-cell">{{ formatDate(product.updated_at) }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-1.5">
                                <Link :href="route('admin.products.edit', product.slug)" class="admin-table-action" aria-label="Edit product"><Pencil class="size-4" /></Link>
                                <button v-if="product.status !== 'archived'" class="admin-table-action" type="button" aria-label="Archive product" @click="pendingAction = { type: 'archive', product }"><Archive class="size-4" /></button>
                                <button v-else class="admin-table-action" type="button" aria-label="Restore product" @click="restore(product)"><RotateCcw class="size-4" /></button>
                                <button class="admin-table-action border-red-200 text-red-600 hover:border-red-300 hover:text-red-700" type="button" aria-label="Permanently delete product" title="Permanently delete" @click="pendingAction = { type: 'delete', product }"><Trash2 class="size-4" /></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </DataTable>
        </div>
        <div class="mt-8"><Pagination :links="products.links" /></div>
        <Teleport to="body">
            <dialog ref="exportDialog" aria-labelledby="products-export-title" class="product-export-dialog m-auto rounded-2xl border border-tabarak-line bg-white p-0 text-tabarak-ink shadow-2xl backdrop:bg-slate-950/55" @cancel.prevent="closeExport">
                <form :action="route('admin.products.export')" method="get" class="product-export-form">
                    <header class="flex items-start justify-between gap-3 border-b border-tabarak-line p-4 sm:p-5">
                        <div>
                            <h2 id="products-export-title" class="font-display text-xl font-bold">Export products to Excel</h2>
                            <p class="mt-1 text-sm text-slate-500">Choose exactly which information to include in one spreadsheet.</p>
                        </div>
                        <button type="button" aria-label="Close export" class="grid size-11 shrink-0 place-items-center rounded-lg hover:bg-tabarak-mist" @click="closeExport"><X class="size-5" /></button>
                    </header>
                    <div class="product-export-body space-y-5 p-4 sm:p-5">
                        <fieldset class="space-y-2">
                            <legend class="text-sm font-bold">Which products?</legend>
                            <label class="flex min-h-12 cursor-pointer items-center gap-3 rounded-lg border border-tabarak-line p-3" :class="{ 'bg-tabarak-mist': exportScope === 'all' }">
                                <input v-model="exportScope" name="scope" value="all" type="radio" class="size-5 shrink-0 text-tabarak-blue focus:ring-tabarak-blue" />
                                <span class="min-w-0"><strong class="block text-sm">All products</strong><span class="block text-xs text-slate-500">Entire catalogue, including drafts and archived products.</span></span>
                            </label>
                            <label class="flex min-h-12 cursor-pointer items-center gap-3 rounded-lg border border-tabarak-line p-3" :class="{ 'bg-tabarak-mist': exportScope === 'filtered' }">
                                <input v-model="exportScope" name="scope" value="filtered" type="radio" class="size-5 shrink-0 text-tabarak-blue focus:ring-tabarak-blue" />
                                <span class="min-w-0"><strong class="block text-sm">Current filtered results ({{ products.total }})</strong><span class="block text-xs text-slate-500">All matching products across every page.</span></span>
                            </label>
                            <template v-if="exportScope === 'filtered'">
                                <input v-if="props.filters.search" type="hidden" name="search" :value="props.filters.search" />
                                <input v-if="props.filters.category" type="hidden" name="category" :value="props.filters.category" />
                                <input v-if="props.filters.brand" type="hidden" name="brand" :value="props.filters.brand" />
                                <input v-if="props.filters.status" type="hidden" name="status" :value="props.filters.status" />
                            </template>
                        </fieldset>
                        <fieldset>
                            <legend class="text-sm font-bold">Columns to export</legend>
                            <label class="my-2 flex min-h-12 cursor-pointer items-center gap-3 rounded-lg bg-tabarak-mist p-3 text-sm font-bold">
                                <input type="checkbox" class="size-5 rounded border-tabarak-line text-tabarak-blue focus:ring-tabarak-blue" :checked="allColumnsSelected" :indeterminate="selectedColumns.length > 0 && !allColumnsSelected" @change="toggleAllColumns" />
                                Select all columns
                                <span class="ml-auto text-xs font-normal text-slate-500">{{ selectedColumns.length }} / {{ exportColumns.length }}</span>
                            </label>
                            <div class="grid gap-2 sm:grid-cols-2">
                                <label v-for="column in exportColumns" :key="column.key" class="flex min-h-11 min-w-0 cursor-pointer items-center gap-2 rounded-lg border border-tabarak-line px-3 py-2 text-sm" :class="{ 'bg-tabarak-mist': selectedColumns.includes(column.key) }">
                                    <input v-model="selectedColumns" type="checkbox" name="columns[]" :value="column.key" class="size-5 shrink-0 rounded border-tabarak-line text-tabarak-blue focus:ring-tabarak-blue" />
                                    <span class="break-words">{{ column.label }}</span>
                                </label>
                            </div>
                        </fieldset>
                        <p class="text-xs leading-5 text-slate-500">The Excel file includes one sheet, sortable headings and numeric USD prices. Missing prices stay blank.</p>
                    </div>
                    <footer class="flex flex-wrap items-center justify-end gap-2 border-t border-tabarak-line bg-white p-4 sm:px-5">
                        <button type="button" class="btn-secondary flex-1 sm:flex-none" @click="closeExport">Cancel</button>
                        <button type="submit" class="btn-primary flex-1 sm:flex-none" :disabled="!selectedColumns.length"><Download class="size-4" /> Download Excel</button>
                    </footer>
                </form>
            </dialog>
        </Teleport>
        <ConfirmDialog
            :open="Boolean(pendingAction)"
            :title="dialogTitle"
            :description="dialogDescription"
            :confirm-label="pendingAction?.type === 'delete' ? 'Delete permanently' : 'Archive product'"
            :tone="pendingAction?.type === 'delete' ? 'danger' : 'warning'"
            :processing="actionProcessing"
            @cancel="pendingAction = null"
            @confirm="confirmAction"
        />
    </AdminLayout>
</template>

<style scoped>
.product-export-dialog {
    width: min(680px, calc(100% - 24px));
    max-height: calc(100vh - 24px);
    max-height: calc(100dvh - 24px);
    overflow: hidden;
}
.product-export-form {
    display: grid;
    grid-template-rows: auto minmax(0, 1fr) auto;
    max-height: calc(100vh - 26px);
    max-height: calc(100dvh - 26px);
    min-height: 0;
}
.product-export-body {
    overflow-y: auto;
    min-height: 0;
    overscroll-behavior-y: contain;
}
</style>
