<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
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
const filter = ref<'all' | 'unpublished' | 'priced' | 'published'>('unpublished');
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
const priced = computed(() => unpublished.value.map(item => drafts[item.id]).filter((draft): draft is ImportPublishDraft => !!draft));

const updateDraft = (id: number, draft: ImportPublishDraft | null) => { drafts[id] = draft; };
const isVisible = (item: ImportItem) => {
    if (filter.value === 'unpublished' && (item.status === 'approved' || item.status === 'rejected')) return false;
    if (filter.value === 'priced' && (!drafts[item.id] || !unpublished.value.some(row => row.id === item.id))) return false;
    if (filter.value === 'published' && item.status !== 'approved') return false;
    const needle = query.value.trim().toLowerCase();
    return !needle || [item.id, item.suggested_name, item.suggested_brand, item.suggested_category, drafts[item.id]?.name, drafts[item.id]?.sku, item.suggested_metadata?.flavor].some(value => String(value ?? '').toLowerCase().includes(needle));
};
const visibleCount = computed(() => items.value.filter(isVisible).length);
const publishPriced = () => {
    if (batchForm.processing || anyRowBusy.value || !priced.value.length) return;
    batchForm.items = priced.value.slice(0, 250);
    confirming.value = false;
    batchForm.post(route('admin.imports.publish.store', props.batch.id), {
        preserveScroll: true,
        onSuccess: () => batchForm.reset(),
        onError: () => document.getElementById('priced-import-errors')?.scrollIntoView({ block: 'center' }),
    });
};
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
                </select>
                <select aria-label="Set open quantity for all items" class="field-input import-filter !min-h-10 !w-auto max-w-full" :disabled="batchForm.processing || anyRowBusy" @change="setAllQuantity">
                    <option value="">Set quantity for all</option>
                    <option value="open">Open quantity for all</option>
                    <option value="closed">Clear open quantity</option>
                </select>
                <button type="button" class="btn-primary import-bulk !min-h-10 !px-4 !py-2 text-xs" :disabled="batchForm.processing || anyRowBusy || !priced.length" @click="confirming = true">
                    <Send class="size-4" /> {{ batchForm.processing ? 'Publishing…' : 'Publish priced (' + Math.min(priced.length, 250) + ')' }}
                </button>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-1 border-b border-tabarak-line bg-tabarak-mist px-3 py-2 text-[11px] font-medium text-slate-600">
                <span>{{ visibleCount }} shown · {{ priced.length }} priced · {{ unpublished.length }} awaiting publication</span>
                <span>Enter moves to next visible price field. Review only when corrections are needed.</span>
            </div>
            <div v-if="batchErrors.length" id="priced-import-errors" role="alert" class="border-b border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700">
                <p class="font-bold">No items were published. Correct the following:</p>
                <ul class="mt-1 list-disc space-y-1 pl-5"><li v-for="error in batchErrors" :key="error.key">{{ error.text }}</li></ul>
            </div>
            <div class="import-list-heading border-b border-tabarak-line bg-white px-4 py-2 text-[11px] font-bold uppercase tracking-wide text-slate-500">
                <span>Image</span><span>Product / analysis</span><span>Price (USD)</span><span class="text-right">Actions</span>
            </div>
            <ImportAnalysisCard v-for="item in items" v-show="isVisible(item)" :key="item.id" :item="item" :categories="categories" :batch-busy="batchForm.processing || (anyRowBusy && !rowBusy[item.id])" :quantity-command="quantityCommand" @draft="updateDraft" @busy="(id, busy) => rowBusy[id] = busy" />
            <p v-if="!visibleCount" class="p-8 text-center text-sm text-slate-500">No items match this view.</p>
        </section>
        <ConfirmDialog :open="confirming" title="Publish priced products?" :description="`Publish ${Math.min(priced.length, 250)} products using their entered prices and current edited details? Unpriced items will remain unpublished. Make sure the selected product details are correct.`" confirm-label="Publish priced items" tone="warning" :processing="batchForm.processing" @cancel="confirming = false" @confirm="publishPriced" />
    </AdminLayout>
</template>

<style scoped>
.import-workspace { container: import-workspace / inline-size; }
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
