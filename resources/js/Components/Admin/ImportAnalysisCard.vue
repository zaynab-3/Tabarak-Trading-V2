<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { AlertTriangle, ExternalLink, Pencil, Send, Trash2, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import StatusBadge from '@/Components/Admin/StatusBadge.vue';
import ConfirmDialog from '@/Components/Shared/ConfirmDialog.vue';
import SelectMenu, { type SelectOption } from '@/Components/Shared/SelectMenu.vue';
import type { ImportItem, TaxonomyRef } from '@/types/catalogue';

const props = defineProps<{ item: ImportItem; categories: TaxonomyRef[] }>();

const reviewOpen = ref(['review', 'failed'].includes(props.item.status) && !props.item.suggested_name);
const confirmingDelete = ref(false);

const matchedCategory = computed(() => props.categories.find(
    (category) => category.name.localeCompare(props.item.suggested_category ?? '', undefined, { sensitivity: 'accent' }) === 0,
) ?? null);

const categoryOptions = computed<SelectOption[]>(() => [
    { value: '', label: 'Uncategorized - admin review needed' },
    ...props.categories.map((category) => ({ value: category.id, label: category.name })),
]);

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

const form = useForm({
    name: props.item.suggested_name ?? '',
    brand: props.item.suggested_brand ?? '',
    category_id: matchedCategory.value?.id ?? '',
    pack_quantity: detectedPackQuantity(),
    allows_open_quantity: false,
});

const deleteForm = useForm({});

const publish = () => form.post(
    route('admin.imports.items.publish.store', [props.item.import_batch_id, props.item.id]),
    { preserveScroll: true },
);

const destroy = () => {
    if (!canDelete.value || deleteForm.processing) {
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
    <article class="surface overflow-hidden">
        <div class="aspect-[4/3] bg-tabarak-mist p-3">
            <img
                :src="item.media.url"
                :alt="item.media.alt_text || `Import image ${item.id}`"
                class="h-full w-full object-contain"
            />
        </div>

        <div class="p-4">
            <div class="flex items-center justify-between gap-3">
                <span class="text-xs font-bold text-slate-400">Item #{{ item.id }}</span>
                <StatusBadge :status="item.status" />
            </div>

            <h2 class="mt-3 font-display text-xl font-bold text-tabarak-ink">
                {{ item.suggested_name || (['pending', 'processing'].includes(item.status) ? 'Analyzing product…' : 'Needs manual identification') }}
            </h2>

            <p class="mt-2 text-sm text-slate-500">
                {{ [item.suggested_brand, item.suggested_category, item.suggested_weight].filter(Boolean).join(' · ') || 'No confident product details yet.' }}
            </p>

            <p v-if="confidenceLabel(item.confidence)" class="mt-2 text-xs font-bold text-emerald-700">
                {{ confidenceLabel(item.confidence) }}
            </p>

            <dl v-if="item.suggested_metadata" class="mt-4 grid grid-cols-2 gap-2 text-xs">
                <template v-for="(value, key) in item.suggested_metadata" :key="key">
                    <div v-if="value" class="rounded bg-tabarak-mist p-2">
                        <dt class="font-bold capitalize text-slate-500">{{ String(key).replaceAll('_', ' ') }}</dt>
                        <dd class="mt-1 line-clamp-3 text-tabarak-ink">{{ value }}</dd>
                    </div>
                </template>
            </dl>

            <p v-if="item.warnings?.length" class="mt-3 border-l-2 border-tabarak-orange pl-3 text-xs leading-5 text-slate-600">
                {{ item.warnings.join(' ') }}
            </p>

            <div v-if="canReview && !item.suggested_name" class="mt-3 flex items-start gap-2 border border-amber-200 bg-amber-50 p-3 text-xs leading-5 text-amber-900">
                <AlertTriangle class="mt-0.5 size-4 shrink-0" />
                <span>AI could not identify this image. Enter the product details manually below, or delete the item if the uploaded image is unusable.</span>
            </div>

            <div v-else-if="item.suggested_name && !matchedCategory" class="mt-3 flex items-start gap-2 border border-amber-200 bg-amber-50 p-3 text-xs leading-5 text-amber-900">
                <AlertTriangle class="mt-0.5 size-4 shrink-0" />
                <span>AI could not assign <strong>{{ item.suggested_name }}</strong> to one of your existing categories. Choose one below or leave it Uncategorized.</span>
            </div>

            <div
                v-if="canReview || canDelete"
                class="mt-4 grid gap-2 border-t border-tabarak-line pt-4"
                :class="canReview && canDelete ? 'grid-cols-2' : 'grid-cols-1'"
            >
                <button
                    v-if="canReview"
                    type="button"
                    class="btn-secondary w-full"
                    :disabled="form.processing || deleteForm.processing"
                    @click="reviewOpen = !reviewOpen"
                >
                    <X v-if="reviewOpen" class="size-4" />
                    <Pencil v-else class="size-4" />
                    {{ reviewOpen ? 'Close review' : 'Review' }}
                </button>

                <button
                    v-if="canDelete"
                    type="button"
                    class="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-md border border-red-200 bg-white px-4 text-sm font-bold text-red-600 transition hover:border-red-300 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="deleteForm.processing || form.processing"
                    @click="confirmingDelete = true"
                >
                    <Trash2 class="size-4" />
                    {{ deleteForm.processing ? 'Deleting…' : 'Delete' }}
                </button>
            </div>

            <form
                v-if="canReview && reviewOpen"
                class="mt-4 space-y-3 rounded-lg border border-tabarak-line bg-white p-4"
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
                    <SelectMenu v-model="form.category_id" :options="categoryOptions" aria-label="Existing category" />
                    <span v-if="form.errors.category_id" class="mt-1 block text-xs text-red-600">{{ form.errors.category_id }}</span>
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

                <button class="btn-primary w-full" type="submit" :disabled="form.processing || deleteForm.processing || !form.name.trim()">
                    <Send class="size-4" />
                    {{ form.processing ? 'Publishing…' : 'Publish to shop' }}
                </button>
            </form>

            <Link
                v-if="item.status === 'approved' && item.approved_product"
                :href="route('admin.products.edit', item.approved_product.slug)"
                class="btn-secondary mt-4 w-full"
            >
                Edit published product <ExternalLink class="size-4" />
            </Link>
        </div>

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
