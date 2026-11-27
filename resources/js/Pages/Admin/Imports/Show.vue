<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft, LoaderCircle, Search, Send, ShieldCheck } from '@lucide/vue';
import { computed, reactive, ref } from 'vue';
import AiAnalyzerStatus from '@/Components/Admin/AiAnalyzerStatus.vue';
import ImportAnalysisCard from '@/Components/Admin/ImportAnalysisCard.vue';
import ConfirmDialog from '@/Components/Shared/ConfirmDialog.vue';
import ReanalyzeImportBatchButton from '@/Components/Admin/ReanalyzeImportBatchButton.vue';
import { useImportBatchPolling } from '@/Composables/useImportBatchPolling';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { ImportBatch, ImportItem, ImportPublishDraft, ProductImageAnalyzerStatus, TaxonomyRef } from '@/types/catalogue';

const props = defineProps<{
    batch: ImportBatch;
    categories: TaxonomyRef[];
    analyzer: ProductImageAnalyzerStatus;
    canReanalyze: boolean;
}>();

const drafts = reactive<Record<number, ImportPublishDraft | null>>({});
const query = ref('');
const requestedMissing = new URLSearchParams(usePage().url.split('?')[1] ?? '').get('view') === 'missing';
const filter = ref<'all' | 'unpublished' | 'priced' | 'published' | 'missing'>(requestedMissing ? 'missing' : props.batch.items?.some(item => ['pending', 'processing', 'review', 'failed'].includes(item.status)) ? 'unpublished' : props.batch.items?.some(item => item.approved_product?.status === 'published' && item.approved_product.unit_price === null) ? 'missing' : 'published');
const confirming = ref(false);
const quantityCommand = ref({ value: false, revision: 0 });
const rowBusy = reactive<Record<number, boolean>>({});
const anyRowBusy = computed(() => Object.values(rowBusy).some(Boolean));
const setAllQuantity = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    if (target.value === '') return;
    quantityCommand.value = { value: target.value === 'open', revision: quantityCommand.value.revision + 1 };
    target.value = '';
};
const batchForm = useForm<{ items: ImportPublishDraft[] }>({ items: [] });
const items = computed(() => props.batch.items ?? []);
const unpublished = computed(() => items.value.filter(item => ['review', 'failed'].includes(item.status) && !item.approved_product_id));
const eligible = computed(() => unpublished.value.map(item => drafts[item.id]).filter((draft): draft is ImportPublishDraft => !!draft));
const priced = computed(() => eligible.value.filter(draft => String(draft.unit_price).trim() !== ''));
const publishing = computed(() => eligible.value.slice(0, 250));
const publishingMissingCount = computed(() => publishing.value.filter(draft => String(draft.unit_price).trim() === '').length);
const missing = computed(() => items.value.filter(item => item.status === 'approved' && item.approved_product?.status === 'published' && item.approved_product.unit_price === null));
const laterPrices = reactive<Record<number, string | number>>({});
const priceForm = useForm<{ items: { id: number; unit_price: string | number }[] }>({ items: [] });
const validLaterPrice = (value: string | number | undefined) => /^\d+(?:\.\d{1,2})?$/.test(String(value ?? '')) && Number(value) >= 0.01 && Number(value) <= 9999999999.99;
const laterDrafts = computed(() => missing.value.filter(item => validLaterPrice(laterPrices[item.id])).map(item => ({ id: item.id, unit_price: laterPrices[item.id] })));
const invalidLaterPrice = computed(() => missing.value.some(item => String(laterPrices[item.id] ?? '').trim() !== '' && !validLaterPrice(laterPrices[item.id])));
const savePrices = () => {
    if (priceForm.processing || batchForm.processing || anyRowBusy.value || invalidLaterPrice.value || !laterDrafts.value.length) return;
    priceForm.items = laterDrafts.value.slice(0, 250);
    const saved = priceForm.items.map(item => item.id);
    priceForm.patch(route('admin.imports.prices.update', props.batch.id), {
        preserveScroll: true,
        onSuccess: () => {
            saved.forEach(id => delete laterPrices[id]);
            priceForm.reset();
            if (!missing.value.length) filter.value = 'published';
        },
        onError: () => document.getElementById('missing-price-errors')?.scrollIntoView({ block: 'center' }),
    });
};
const focusNextLaterPrice = (event: KeyboardEvent) => {
    const inputs = Array.from(document.querySelectorAll<HTMLInputElement>('[data-later-price]')).filter(input => !input.disabled && input.offsetParent !== null);
    const index = inputs.indexOf(event.currentTarget as HTMLInputElement);
    if (index >= 0 && index < inputs.length - 1) inputs[index + 1].focus();
};

const updateDraft = (id: number, draft: ImportPublishDraft | null) => { drafts[id] = draft; };
const isVisible = (item: ImportItem) => {
    if (filter.value === 'unpublished' && (item.status === 'approved' || item.status === 'rejected')) return false;
    if (filter.value === 'priced' && !priced.value.some(row => row.id === item.id)) return false;
    if (filter.value === 'missing' && !missing.value.some(row => row.id === item.id)) return false;
    if (filter.value === 'published' && item.status !== 'approved') return false;
    const needle = query.value.trim().toLowerCase();
    return !needle || [item.id, item.suggested_name, item.suggested_brand, item.suggested_category, drafts[item.id]?.name, drafts[item.id]?.sku, item.suggested_metadata?.flavor].some(value => String(value ?? '').toLowerCase().includes(needle));
};
const visibleCount = computed(() => items.value.filter(isVisible).length);
const publishPriced = () => {
    if (batchForm.processing || priceForm.processing || anyRowBusy.value || !publishing.value.length) return;
    batchForm.items = publishing.value;
    const hasMissing = publishingMissingCount.value > 0;
    confirming.value = false;
    batchForm.post(route('admin.imports.publish.store', props.batch.id), {
        preserveScroll: true,
        onSuccess: () => { batchForm.reset(); if (hasMissing && missing.value.length) { filter.value = 'missing'; query.value = ''; } },
        onError: () => document.getElementById('priced-import-errors')?.scrollIntoView({ block: 'center' }),
    });
};
const priceErrors = computed(() => Object.entries(priceForm.errors).map(([key, message]) => {
    const match = key.match(/^items\.(\d+)\./);
    const item = match ? priceForm.items[Number(match[1])] : null;
    return { key, text: (item ? `Item #${item.id}: ` : '') + message };
}));
const batchErrors = computed(() => Object.entries(batchForm.errors).map(([key, message]) => {
    const match = key.match(/^items\.(\d+)\./);
    const draft = match ? batchForm.items[Number(match[1])] : null;
    return { key, text: (draft ? `Item #${draft.id}: ` : '') + message };
}));

useImportBatchPolling(
    () => props.batch.total_items > 0 && props.batch.processed_items < props.batch.total_items,
);
</script>

<template>
    <Head :title="batch.name || `Import #${batch.id}`" />
    <AdminLayout>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="eyebrow">Import workspace</p>
                <h1 class="mt-1 break-words font-display text-2xl font-bold text-tabarak-ink">{{ batch.name || `Import #${batch.id}` }}</h1>
                <p class="mt-1 text-xs text-slate-500">{{ batch.processed_items }} of {{ batch.total_items }} analyzed · {{ batch.approved_items }} published</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <div :class="{ 'pointer-events-none opacity-50': batchForm.processing || anyRowBusy }"><ReanalyzeImportBatchButton v-if="canReanalyze" :batch-id="batch.id" /></div>
                <Link :href="route('admin.imports.index')" class="btn-secondary !min-h-9 !px-3 !py-1 text-xs"><ArrowLeft class="size-4" /> All batches</Link>
            </div>
        </div>

        <details class="mb-4 rounded-lg border border-tabarak-line bg-white p-3 text-xs text-slate-600">
            <summary class="cursor-pointer font-semibold">{{ analyzer.enabled ? analyzer.provider + ' active' : 'Manual review mode' }} · Import settings &amp; rules</summary>
            <AiAnalyzerStatus :analyzer="analyzer" class="mt-3" />
            <p class="mt-3 flex items-start gap-2 leading-5"><ShieldCheck class="size-4 shrink-0 text-tabarak-blue" /> Existing active categories only; brands may be created when publishing. AI information must be checked by the administrator.</p>
        </details>

        <div v-if="batch.processed_items < batch.total_items" class="mb-3 flex items-center gap-2 rounded-lg border border-tabarak-line bg-tabarak-mist p-3 text-xs font-semibold text-tabarak-blue">
            <LoaderCircle class="size-4 animate-spin" /> Analysis is running. Results refresh automatically.
        </div>

        <div v-if="missing.length" class="mb-3 flex flex-wrap items-center justify-between gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
            <span><strong>{{ missing.length }} published product(s) missing a price.</strong> Add prices whenever you are ready.</span>
            <button type="button" class="btn-secondary !min-h-10 !px-3 !py-1 text-xs" @click="filter = 'missing'; query = ''">Add missing prices</button>
        </div>
        <section class="import-workspace rounded-xl border border-tabarak-line bg-white">
            <div class="import-toolbar sticky z-20 flex flex-wrap items-center gap-2 rounded-t-xl border-b border-tabarak-line bg-white p-3">
                <div class="import-search relative min-w-0 flex-1">
                    <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                    <input v-model="query" type="search" aria-label="Search import items" placeholder="Search name, ID, category…" class="field-input !min-h-10 !pl-9" />
                </div>
                <select v-model="filter" aria-label="Filter import items" class="field-input import-filter !min-h-10 !w-auto max-w-full">
                    <option value="unpublished">Unpublished</option>
                    <option value="all">All items</option>
                    <option value="priced">Priced &amp; ready</option>
                    <option value="published">Published</option>
                    <option value="missing">Missing prices ({{ missing.length }})</option>
                </select>
                <select v-if="filter !== 'missing'" aria-label="Set open quantity for all items" class="field-input import-filter !min-h-10 !w-auto max-w-full" :disabled="batchForm.processing || anyRowBusy" @change="setAllQuantity">
                    <option value="">Set quantity for all</option>
                    <option value="open">Open quantity for all</option>
                    <option value="closed">Clear open quantity</option>
                </select>
                <button v-if="filter === 'missing'" type="button" class="btn-primary import-bulk !min-h-10 !px-4 !py-2 text-xs" :disabled="priceForm.processing || batchForm.processing || anyRowBusy || invalidLaterPrice || !laterDrafts.length" @click="savePrices">
                    {{ priceForm.processing ? 'Saving…' : 'Save prices (' + Math.min(laterDrafts.length, 250) + ')' }}
                </button>
                <button v-else type="button" class="btn-primary import-bulk !min-h-10 !px-4 !py-2 text-xs" :disabled="batchForm.processing || priceForm.processing || anyRowBusy || !publishing.length" @click="confirming = true">
                    <Send class="size-4" /> {{ batchForm.processing ? 'Publishing…' : 'Publish all ready (' + publishing.length + ')' }}
                </button>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-1 border-b border-tabarak-line bg-tabarak-mist px-3 py-2 text-[11px] font-medium text-slate-600">
                <span v-if="filter === 'missing'">{{ visibleCount }} shown · {{ laterDrafts.length }} prices entered</span>
                <span v-else>{{ visibleCount }} shown · {{ priced.length }} priced · {{ unpublished.length }} awaiting publication</span>
                <span>{{ filter === 'missing' ? 'Enter prices and save together. Blank fields remain unchanged.' : 'Prices are optional. Enter moves to the next visible price field.' }}</span>
            </div>
            <div v-if="batchErrors.length" id="priced-import-errors" role="alert" class="border-b border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700">
                <p class="font-bold">No items were published. Correct the following:</p>
                <ul class="mt-1 list-disc space-y-1 pl-5"><li v-for="error in batchErrors" :key="error.key">{{ error.text }}</li></ul>
            </div>
            <div v-if="priceErrors.length && filter === 'missing'" id="missing-price-errors" role="alert" class="border-b border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700">
                <ul class="list-disc space-y-1 pl-5"><li v-for="error in priceErrors" :key="error.key">{{ error.text }}</li></ul>
            </div>
            <div v-if="filter === 'missing'" class="divide-y divide-tabarak-line">
                <div v-for="item in missing.filter(isVisible)" :key="'price-' + item.id" class="later-price-row">
                    <img :src="item.media.url" :alt="item.media.alt_text || item.suggested_name || 'Product'" loading="lazy" decoding="async" class="size-14 rounded-lg bg-tabarak-mist object-contain" />
                    <div class="min-w-0">
                        <p class="break-words text-sm font-bold text-tabarak-ink">{{ item.approved_product?.name }}</p>
                        <p class="text-xs text-slate-500">#{{ item.id }} · Missing price</p>
                    </div>
                    <label class="min-w-0">
                        <span class="sr-only">USD price for {{ item.approved_product?.name }}</span>
                        <div class="flex items-center gap-1 rounded-lg border border-tabarak-line px-2">
                            <span class="text-xs text-slate-500">$</span>
                            <input v-model="laterPrices[item.id]" data-later-price class="min-h-11 w-full min-w-0 border-0 bg-transparent px-0 text-base focus:ring-0" type="number" inputmode="decimal" min="0.01" max="9999999999.99" step="0.01" placeholder="Price" :disabled="priceForm.processing || batchForm.processing || anyRowBusy" @keydown.enter.prevent="focusNextLaterPrice" />
                        </div>
                        <span v-if="String(laterPrices[item.id] ?? '').trim() && !validLaterPrice(laterPrices[item.id])" class="text-xs text-red-600">Invalid price</span>
                    </label>
                </div>
            </div>
            <div v-show="filter !== 'missing'" class="import-list-heading border-b border-tabarak-line bg-white px-4 py-2 text-[11px] font-bold uppercase tracking-wide text-slate-500">
                <span>Image</span><span>Product / analysis</span><span>Price (USD)</span><span class="text-right">Actions</span>
            </div>
            <ImportAnalysisCard v-for="item in items" v-show="filter !== 'missing' && isVisible(item)" :key="item.id" :item="item" :categories="categories" :batch-busy="priceForm.processing || batchForm.processing || (anyRowBusy && !rowBusy[item.id])" :quantity-command="quantityCommand" @draft="updateDraft" @busy="(id, busy) => rowBusy[id] = busy" />
            <p v-if="!visibleCount" class="p-8 text-center text-sm text-slate-500">No items match this view.</p>
        </section>
        <ConfirmDialog :open="confirming" title="Publish products?" :description="`Publish ${publishing.length} products using their current details, including ${publishingMissingCount} without prices? Missing prices can be added afterward. Products without prices cannot be ordered until priced.`" confirm-label="Publish products" tone="warning" :processing="batchForm.processing" @cancel="confirming = false" @confirm="publishPriced" />
    </AdminLayout>
</template>

<style scoped>
.import-workspace { container: import-workspace / inline-size; }
.later-price-row { display: grid; grid-template-columns: 56px minmax(0, 1fr) minmax(85px, 140px); align-items: center; gap: 10px; padding: 10px 12px; }
@container import-workspace (max-width: 360px) {
    .later-price-row { grid-template-columns: 48px minmax(0, 1fr); }
    .later-price-row label { grid-column: 2; }
}
.import-toolbar { top: 72px; }
.import-search { flex-basis: 180px; }
.import-list-heading { display: none; }
@container import-workspace (min-width: 690px) {
    .import-list-heading {
        display: grid;
        grid-template-columns: 64px minmax(0, 1fr) 112px 236px;
        gap: 14px;
    }
}
@container import-workspace (max-width: 479px) {
    .import-search { flex-basis: 100%; }
    .import-filter { flex: 1 1 130px; }
    .import-bulk { flex: 1 1 100%; }
}
@media (pointer: coarse) {
    .import-toolbar input, .import-toolbar select { font-size: 16px; min-height: 44px !important; }
    .import-toolbar button { min-height: 44px !important; }
}
</style>
