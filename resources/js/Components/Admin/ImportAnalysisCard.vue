<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { AlertTriangle, ExternalLink, Pencil, Send, Trash2, X } from '@lucide/vue';
import { computed, nextTick, ref, watch } from 'vue';
import StatusBadge from '@/Components/Admin/StatusBadge.vue';
import ConfirmDialog from '@/Components/Shared/ConfirmDialog.vue';
import type { ImportItem, ImportPublishDraft, TaxonomyRef } from '@/types/catalogue';

const props = defineProps<{ item: ImportItem; categories: TaxonomyRef[]; batchBusy?: boolean; quantityCommand?: { value: boolean; revision: number } }>();
const emit = defineEmits<{ draft: [id: number, draft: ImportPublishDraft | null]; busy: [id: number, busy: boolean] }>();

const reviewDialog = ref<HTMLDialogElement | null>(null);
const reviewMounted = ref(false);
const mobileReviewTab = ref<'details' | 'analysis'>('details');
const confirmingDelete = ref(false);

const matchedCategory = computed(() => props.categories.find(
    (category) => category.name.localeCompare(props.item.suggested_category ?? '', undefined, { sensitivity: 'accent' }) === 0,
) ?? null);

const canReview = computed(
    () => ['review', 'failed'].includes(props.item.status) && !props.item.approved_product_id,
);

const canDelete = computed(
    () => ['review', 'failed'].includes(props.item.status) && !props.item.approved_product_id,
);

const detectedPackQuantity = () => {
    const value = props.item.suggested_metadata?.pack_quantity;
    const match = String(value ?? '').match(/\d+/);

    return match ? Number(match[0]) : '';
};

const suggestedFormValues = () => {
    const metadata = props.item.suggested_metadata;
    const weight = (props.item.suggested_weight ?? '').match(/(\d+(?:[.,]\d+)?)\s*(kg|g|ml|l|oz)\b/i);
    return {
        name: props.item.suggested_name ?? '',
        brand: props.item.suggested_brand ?? '',
        category_id: matchedCategory.value?.id ?? '',
        pack_quantity: detectedPackQuantity(),
        sku: metadata?.sku ?? '',
        short_description: metadata?.description?.slice(0, 500) ?? '',
        description: metadata?.detected_text ? `Visible package text: ${metadata.detected_text}`.slice(0, 10000) : '',
        weight_value: weight ? Number(weight[1].replace(',', '.')) : '',
        weight_unit: weight?.[2]?.toLowerCase() ?? '',
    };
};

const form = useForm({
    ...suggestedFormValues(),
    unit_price: '' as string | number,
    allows_open_quantity: props.quantityCommand?.value ?? false,
});
watch(() => props.quantityCommand?.revision, () => {
    if (props.quantityCommand && canReview.value) form.allows_open_quantity = props.quantityCommand.value;
});

// Update fields that still contain the previous AI suggestion as results arrive.
// A price or manually corrected field never prevents other suggestions from loading.
watch(suggestedFormValues, (next, previous) => {
    for (const key of Object.keys(next) as (keyof typeof next)[]) {
        if (form[key] === previous[key]) Object.assign(form, { [key]: next[key] });
    }
});

const priceValid = computed(() => /^\d+(?:\.\d{1,2})?$/.test(String(form.unit_price))
    && Number(form.unit_price) >= 0.01 && Number(form.unit_price) <= 9999999999.99);
const deleteForm = useForm({});
const canQuickPublish = computed(() => canReview.value && !!form.name.trim());
const rowCategory = computed(() => props.categories.find(category => category.id === Number(form.category_id))?.name ?? 'Uncategorized');
const draft = computed<ImportPublishDraft | null>(() => canQuickPublish.value && priceValid.value && !form.processing && !deleteForm.processing ? { id: props.item.id, ...form.data() } : null);
watch(draft, (value) => emit('draft', props.item.id, value), { immediate: true });
watch(() => form.processing || deleteForm.processing, value => emit('busy', props.item.id, value));
const focusNextPrice = (event: KeyboardEvent) => {
    const inputs = Array.from(document.querySelectorAll<HTMLInputElement>('[data-import-price]')).filter(input => !input.disabled && input.offsetParent !== null);
    const index = inputs.indexOf(event.currentTarget as HTMLInputElement);
    if (index >= 0 && index < inputs.length - 1) {
        inputs[index + 1].focus();
        inputs[index + 1].select();
    }
};


const openReview = async () => {
    if (!canReview.value || deleteForm.processing || props.batchBusy) return;
    mobileReviewTab.value = 'details';
    reviewMounted.value = true;
    await nextTick();
    const dialog = reviewDialog.value;
    if (dialog && !dialog.open) {
        dialog.showModal();
        dialog.querySelectorAll<HTMLElement>('.review-panel').forEach((panel) => { panel.scrollTop = 0; });
        dialog.querySelector<HTMLButtonElement>('[data-review-close]')?.focus({ preventScroll: true });
    }
};

const closeReview = () => {
    if (!form.processing) reviewDialog.value?.close();
};

const publish = () => {
    if (!canReview.value || form.processing || deleteForm.processing || props.batchBusy || !form.name.trim() || !priceValid.value) return;
    form.post(
        route('admin.imports.items.publish.store', [props.item.import_batch_id, props.item.id]),
        { preserveScroll: true, onSuccess: () => reviewDialog.value?.close(), onError: () => openReview() },
    );
};

const destroy = () => {
    if (!canDelete.value || deleteForm.processing || props.batchBusy) {
        return;
    }

    deleteForm.delete(
        route('admin.imports.items.destroy', [props.item.import_batch_id, props.item.id]),
        {
            preserveScroll: true,
            onSuccess: () => { confirmingDelete.value = false; },
        },
    );
};

const confidenceLabel = (confidence: string | null) => {
    if (!confidence) {
        return null;
    }

    return `${Math.round(Number(confidence) * 100)}% confidence`;
};
</script>

<template>
    <article class="import-row border-b border-tabarak-line bg-white last:border-b-0">
        <button type="button" class="import-thumbnail rounded-lg bg-tabarak-mist p-1" :disabled="!canReview || batchBusy" :aria-label="'Review image for item ' + item.id" @click="openReview">
            <img :src="item.media.url" :alt="item.media.alt_text || 'Import image ' + item.id" loading="lazy" decoding="async" class="h-full w-full object-contain" />
        </button>
        <div class="import-summary min-w-0">
            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <span class="text-[11px] font-semibold text-slate-400">#{{ item.id }}</span>
                <StatusBadge :status="item.status === 'approved' ? 'published' : item.status" />
                <span v-if="confidenceLabel(item.confidence)" class="text-[11px] font-semibold text-slate-500">{{ confidenceLabel(item.confidence) }}</span>
            </div>
            <h2 class="mt-1 break-words text-sm font-bold leading-5 text-tabarak-ink">{{ (item.status === 'approved' ? item.approved_product?.name : form.name) || item.suggested_name || (['pending', 'processing'].includes(item.status) ? 'Analyzing product…' : 'Needs manual identification') }}</h2>
            <p class="mt-0.5 break-words text-xs text-slate-500">{{ [form.brand, rowCategory, form.weight_value !== '' ? String(form.weight_value) + form.weight_unit : null, item.suggested_metadata?.flavor].filter(Boolean).join(' · ') }}</p>
            <button v-if="canReview && (!form.name.trim() || !form.category_id || item.warnings?.length)" type="button" class="mt-1 flex max-w-full items-center gap-1 text-left text-[11px] font-semibold text-tabarak-orange" :disabled="batchBusy" @click="openReview">
                <AlertTriangle class="size-3 shrink-0" />
                <span class="truncate">{{ !form.name.trim() ? 'Name required' : !form.category_id ? 'Category not matched' : (item.warnings?.length || 0) + ' analysis warning(s)' }}</span>
            </button>
        </div>
        <div v-if="canReview" class="import-price min-w-0 space-y-1">
            <label class="block">
                <span class="sr-only">Price in USD for item {{ item.id }}</span>
                <div class="flex items-center gap-1 rounded-lg border border-tabarak-line bg-white px-2 focus-within:ring-1 focus-within:ring-tabarak-blue">
                    <span class="text-xs text-slate-500">$</span>
                    <input v-model="form.unit_price" data-import-price class="import-price-input min-w-0 w-full border-0 bg-transparent px-0 py-2 font-semibold focus:ring-0" type="number" inputmode="decimal" min="0.01" max="9999999999.99" step="0.01" placeholder="0.00" :disabled="form.processing || deleteForm.processing || batchBusy" @keydown.enter.prevent="focusNextPrice" />
                </div>
            </label>
            <label class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-600">
                <input v-model="form.allows_open_quantity" type="checkbox" class="rounded border-tabarak-line" :disabled="form.processing || deleteForm.processing || batchBusy" /> Open qty
            </label>
            <p v-if="form.errors.unit_price" class="text-xs text-red-600">{{ form.errors.unit_price }}</p>
        </div>
        <div v-else class="import-price text-sm font-semibold text-slate-500">
            {{ item.approved_product?.unit_price != null ? '$' + Number(item.approved_product.unit_price).toFixed(2) : '—' }}
        </div>
        <div class="import-actions flex flex-wrap items-center justify-end gap-1.5">
            <button v-if="canReview" type="button" class="btn-secondary !min-h-9 !px-3 !py-1 text-xs" :disabled="form.processing || deleteForm.processing || batchBusy" @click="openReview"><Pencil class="size-3.5" /> Review</button>
            <button v-if="canQuickPublish" type="button" class="btn-primary !min-h-9 !px-3 !py-1 text-xs" :disabled="!priceValid || form.processing || deleteForm.processing || batchBusy" @click="publish"><Send class="size-3.5" /> {{ form.processing ? 'Saving…' : 'Publish' }}</button>
            <button v-if="canDelete" type="button" class="import-delete grid size-9 place-items-center rounded-lg border border-red-200 text-red-600 hover:bg-red-50 disabled:opacity-50" :disabled="deleteForm.processing || form.processing || batchBusy" :aria-label="'Delete item ' + item.id" title="Delete item" @click="confirmingDelete = true"><Trash2 class="size-4" /></button>
            <Link v-if="item.status === 'approved' && item.approved_product" :href="route('admin.products.edit', item.approved_product.slug)" class="btn-secondary !min-h-9 !px-3 !py-1 text-xs">Edit <ExternalLink class="size-3.5" /></Link>
        </div>

        <Teleport to="body">
            <dialog
                v-if="canReview && reviewMounted"
                ref="reviewDialog"
                :aria-labelledby="`import-review-title-${item.id}`"
                class="review-dialog m-auto w-11/12 max-w-5xl rounded-2xl border border-tabarak-line bg-white p-0 text-left text-tabarak-ink shadow-2xl backdrop:bg-slate-950/55"
                @cancel.prevent="closeReview"
                @close="reviewMounted = false"
            >
                <header class="flex items-start justify-between gap-3 border-b border-tabarak-line px-5 py-4 sm:px-6">
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase text-slate-500">Import item #{{ item.id }}</p>
                        <h2 :id="`import-review-title-${item.id}`" class="mt-1 font-display text-xl font-bold">Review product</h2>
                        <p class="mt-1 text-sm text-slate-600">Check the AI analysis, correct the details, then publish.</p>
                    </div>
                    <button type="button" class="grid size-10 shrink-0 place-items-center rounded-lg hover:bg-tabarak-mist" data-review-close aria-label="Close review" :disabled="form.processing" @click="closeReview"><X class="size-5" /></button>
                </header>
                <div class="review-mobile-tabs grid grid-cols-2 gap-1 border-b border-tabarak-line bg-tabarak-mist p-2" aria-label="Review sections">
                    <button type="button" class="rounded-lg px-2 py-2.5 text-sm font-semibold" :class="mobileReviewTab === 'details' ? 'bg-white text-tabarak-ink shadow-sm' : 'text-slate-500'" :aria-pressed="mobileReviewTab === 'details'" @click="mobileReviewTab = 'details'">Product details</button>
                    <button type="button" class="rounded-lg px-2 py-2.5 text-sm font-semibold" :class="mobileReviewTab === 'analysis' ? 'bg-white text-tabarak-ink shadow-sm' : 'text-slate-500'" :aria-pressed="mobileReviewTab === 'analysis'" @click="mobileReviewTab = 'analysis'">AI analysis</button>
                </div>
                <div class="review-body">
                    <section class="review-panel review-reference min-w-0 space-y-4 select-text p-4 sm:p-6" :class="{ 'mobile-panel-hidden': mobileReviewTab !== 'analysis' }" aria-label="AI detected information" tabindex="0">
                        <div class="rounded-xl bg-tabarak-mist p-3">
                            <img :src="item.media.url" :alt="item.media.alt_text || `Import image ${item.id}`" class="mx-auto h-48 w-full object-contain sm:h-56" />
                        </div>
                        <h3 class="font-display text-lg font-bold">AI detected information</h3>
                        <p v-if="confidenceLabel(item.confidence)" class="text-xs font-bold text-emerald-700">{{ confidenceLabel(item.confidence) }}</p>
                        <p class="text-xs leading-5 text-slate-500">Reference only. Select and copy any text below. AI suggestions may be inaccurate.</p>
                        <dl class="grid gap-2 text-sm sm:grid-cols-2">
                            <div class="rounded-lg bg-tabarak-mist p-3 sm:col-span-2">
                                <dt class="text-xs font-bold text-slate-500">Suggested name</dt>
                                <dd class="mt-1 whitespace-pre-wrap break-words">{{ item.suggested_name || 'Not identified' }}</dd>
                            </div>
                            <div v-for="entry in [{ label: 'Suggested brand', value: item.suggested_brand }, { label: 'Suggested category', value: item.suggested_category }, { label: 'Detected weight', value: item.suggested_weight }]" :key="entry.label" class="rounded-lg bg-tabarak-mist p-3">
                                <dt class="text-xs font-bold text-slate-500">{{ entry.label }}</dt>
                                <dd class="mt-1 whitespace-pre-wrap break-words">{{ entry.value || 'Not identified' }}</dd>
                            </div>
                            <template v-for="(value, key) in item.suggested_metadata" :key="key">
                                <div v-if="value !== null && value !== undefined && value !== ''" class="rounded-lg bg-tabarak-mist p-3 sm:col-span-2">
                                    <dt class="text-xs font-bold capitalize text-slate-500">{{ String(key).replaceAll('_', ' ') }}</dt>
                                    <dd class="mt-1 whitespace-pre-wrap break-words">{{ value }}</dd>
                                </div>
                            </template>
                        </dl>
                        <div v-if="item.warnings?.length" class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                            <h4 class="font-bold">Analysis warnings</h4>
                            <ul class="mt-2 list-disc space-y-1 pl-5">
                                <li v-for="(warning, index) in item.warnings" :key="index" class="break-words">{{ warning }}</li>
                            </ul>
                        </div>
                    </section>
                    <section class="review-panel min-w-0 space-y-4 p-4 sm:p-6" :class="{ 'mobile-panel-hidden': mobileReviewTab !== 'details' }" aria-label="Editable product details" tabindex="0">
                        <h3 class="font-display text-lg font-bold">Product details</h3>
                        <p class="text-xs leading-5 text-slate-500">These details will appear in the shop.</p>
                        <form
                            :id="`import-review-form-${item.id}`"
                            class="min-w-0 space-y-4"
                            :aria-busy="form.processing"
                            @submit.prevent="publish"
                        >
                            <label class="block">
                                <span class="field-label">Product name</span>
                                <input
                                    v-model="form.name"
                                    class="field-input"
                                    maxlength="180"
                                    placeholder="Enter product name"
                                    required
                                />
                                <span v-if="form.errors.name" class="mt-1 block text-xs text-red-600">{{ form.errors.name }}</span>
                            </label>

                            <label class="block">
                                <span class="field-label">Price (USD)</span>
                                <input v-model="form.unit_price" class="field-input" type="number" inputmode="decimal" min="0.01" max="9999999999.99" step="0.01" placeholder="0.00" required />
                                <span v-if="form.errors.unit_price" class="mt-1 block text-xs text-red-600">{{ form.errors.unit_price }}</span>
                            </label>

                            <label class="block">
                                <span class="field-label">Brand</span>
                                <input
                                    v-model="form.brand"
                                    class="field-input"
                                    maxlength="120"
                                    placeholder="Optional"
                                />
                                <span class="mt-1 block text-xs text-slate-500">Existing brands are reused; a new brand is created only when needed.</span>
                                <span v-if="form.errors.brand" class="mt-1 block text-xs text-red-600">{{ form.errors.brand }}</span>
                            </label>

                            <label class="block">
                                <span class="field-label">Existing category</span>
                                <select v-model="form.category_id" class="field-input" aria-label="Existing category">
                                    <option value="">Uncategorized - admin review needed</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                                </select>
                                <span v-if="form.errors.category_id" class="mt-1 block text-xs text-red-600">{{ form.errors.category_id }}</span>
                            </label>

                            <label class="block">
                                <span class="field-label">SKU / Item code</span>
                                <input v-model="form.sku" class="field-input" maxlength="100" placeholder="Optional" />
                                <span v-if="form.errors.sku" class="mt-1 block text-xs text-red-600">{{ form.errors.sku }}</span>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="block">
                                    <span class="field-label">Net weight</span>
                                    <input v-model="form.weight_value" class="field-input" type="number" min="0" max="99999999.99" step="any" placeholder="Optional" />
                                    <span v-if="form.errors.weight_value" class="mt-1 block text-xs text-red-600">{{ form.errors.weight_value }}</span>
                                </label>
                                <label class="block">
                                    <span class="field-label">Weight unit</span>
                                    <select v-model="form.weight_unit" class="field-input">
                                        <option value="">Select unit</option>
                                        <option v-for="unit in ['g', 'kg', 'ml', 'l', 'oz']" :key="unit" :value="unit">{{ unit }}</option>
                                    </select>
                                    <span v-if="form.errors.weight_unit" class="mt-1 block text-xs text-red-600">{{ form.errors.weight_unit }}</span>
                                </label>
                            </div>
                            <label class="block">
                                <span class="field-label">Short description</span>
                                <textarea v-model="form.short_description" class="field-input min-h-24" maxlength="500" placeholder="Optional"></textarea>
                                <span v-if="form.errors.short_description" class="mt-1 block text-xs text-red-600">{{ form.errors.short_description }}</span>
                            </label>
                            <label class="block">
                                <span class="field-label">Description / Package text</span>
                                <textarea v-model="form.description" class="field-input min-h-24" maxlength="10000" placeholder="Optional"></textarea>
                                <span v-if="form.errors.description" class="mt-1 block text-xs text-red-600">{{ form.errors.description }}</span>
                            </label>

                            <div class="grid grid-cols-[1fr_auto] gap-3">
                                <label class="block">
                                    <span class="field-label">Pack quantity</span>
                                    <input
                                        v-model="form.pack_quantity"
                                        class="field-input"
                                        min="1"
                                        max="100000"
                                        type="number"
                                        placeholder="Optional"
                                    />
                                    <span v-if="form.errors.pack_quantity" class="mt-1 block text-xs text-red-600">{{ form.errors.pack_quantity }}</span>
                                </label>

                                <label class="flex items-end gap-2 pb-3 text-xs font-bold text-tabarak-ink">
                                    <input
                                        v-model="form.allows_open_quantity"
                                        type="checkbox"
                                        class="rounded border-tabarak-line text-tabarak-blue focus:ring-tabarak-blue"
                                    />
                                    Open quantity
                                </label>
                            </div>

                        </form>
                    </section>
                </div>
                <footer class="flex flex-wrap items-center justify-end gap-2 border-t border-tabarak-line bg-white px-5 py-3 sm:px-6">
                    <button class="btn-secondary flex-1 sm:flex-none" type="button" :disabled="form.processing" @click="closeReview">Keep edits</button>
                    <button class="btn-primary flex-1 sm:flex-none" type="submit" :form="`import-review-form-${item.id}`" :disabled="form.processing || deleteForm.processing || !form.name.trim() || !priceValid">
                        <Send class="size-4" />
                        {{ form.processing ? 'Publishing…' : 'Publish to shop' }}
                    </button>
                </footer>
            </dialog>
        </Teleport>

        <ConfirmDialog
            :open="confirmingDelete"
            title="Delete this import item?"
            description="The item will be removed from this batch. Its image file will also be deleted when it is not used anywhere else. This cannot be undone."
            confirm-label="Delete item"
            :processing="deleteForm.processing"
            @cancel="confirmingDelete = false"
            @confirm="destroy"
        />
    </article>
</template>

<style scoped>
.review-dialog {
    height: min(760px, calc(100vh - 32px));
    height: min(760px, calc(100dvh - 32px));
    max-height: none;
    overflow: hidden;
}

.review-dialog[open] {
    display: grid;
    grid-template-rows: auto auto minmax(0, 1fr) auto;
}

.review-body {
    display: grid;
    min-height: 0;
    min-width: 0;
    grid-template-rows: minmax(0, 1fr);
    overflow: hidden;
}

.review-panel {
    min-height: 0;
    overflow-y: auto;
    overscroll-behavior-y: contain;
    scrollbar-gutter: stable;
    -webkit-overflow-scrolling: touch;
}

.review-reference {
    border-bottom: 1px solid #e5e2dc;
}

@media (max-width: 767px) {
    .review-dialog { width: calc(100% - 16px); }
    .mobile-panel-hidden { display: none; }
    .review-panel :deep(input:not([type="checkbox"])), .review-panel :deep(select), .review-panel :deep(textarea) { font-size: 16px; }
}
@media (min-width: 768px) {
    .review-dialog[open] { grid-template-rows: auto minmax(0, 1fr) auto; }
    .review-mobile-tabs { display: none; }
    .review-body {
        grid-template-rows: minmax(0, 1fr);
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    }

    .review-reference {
        border-bottom: 0;
        border-right: 1px solid #e5e2dc;
    }
}

.import-row {
    display: grid;
    grid-template-columns: 60px minmax(0, 1fr);
    gap: 8px 12px;
    align-items: center;
    padding: 10px 12px;
}
.import-thumbnail { width: 60px; height: 64px; }
.import-price { grid-column: 1 / -1; }
.import-actions { grid-column: 1 / -1; justify-content: flex-start; }
.import-price-input { font-size: 16px; min-height: 42px; }
.import-summary { overflow-wrap: anywhere; }
@container import-workspace (min-width: 480px) {
    .import-row {
        grid-template-columns: 64px minmax(0, 1fr) minmax(100px, 135px);
        gap: 8px 12px;
    }
    .import-price { grid-column: auto; }
    .import-actions { grid-column: 2 / -1; justify-content: flex-end; }
}
@container import-workspace (min-width: 690px) {
    .import-row {
        grid-template-columns: 64px minmax(0, 1fr) 112px 236px;
        gap: 14px;
        padding: 10px 16px;
    }
    .import-thumbnail { width: 64px; height: 64px; }
    .import-price, .import-actions { grid-column: auto; }
}
@media (pointer: coarse) {
    .import-actions button, .import-actions a { min-height: 44px !important; }
    .import-delete { min-width: 44px; }
    .import-price-input { min-height: 44px; }
    .import-price input[type="checkbox"] { width: 20px; height: 20px; }
    .review-panel :deep(input:not([type="checkbox"])), .review-panel :deep(select), .review-panel :deep(textarea) { font-size: 16px; }
}
</style>
