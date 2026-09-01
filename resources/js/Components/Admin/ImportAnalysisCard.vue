<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { AlertTriangle, ExternalLink, Send } from '@lucide/vue';
import { computed } from 'vue';
import StatusBadge from '@/Components/Admin/StatusBadge.vue';
import SelectMenu, { type SelectOption } from '@/Components/Shared/SelectMenu.vue';
import type { ImportItem, TaxonomyRef } from '@/types/catalogue';

const props = defineProps<{ item: ImportItem; categories: TaxonomyRef[] }>();

const matchedCategory = computed(() => props.categories.find(
    (category) => category.name.localeCompare(props.item.suggested_category ?? '', undefined, { sensitivity: 'accent' }) === 0,
) ?? null);
const categoryOptions = computed<SelectOption[]>(() => [
    { value: '', label: 'Uncategorized — admin review needed' },
    ...props.categories.map((category) => ({ value: category.id, label: category.name })),
]);

const detectedPackQuantity = () => {
    const value = props.item.suggested_metadata?.pack_quantity;
    const match = String(value ?? '').match(/\d+/);

    return match ? Number(match[0]) : '';
};

const form = useForm({
    name: props.item.suggested_name ?? '',
    category_id: matchedCategory.value?.id ?? '',
    pack_quantity: detectedPackQuantity(),
    allows_open_quantity: false,
});

const publish = () => form.post(
    route('admin.imports.items.publish.store', [props.item.import_batch_id, props.item.id]),
    { preserveScroll: true },
);

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
            <div v-if="item.suggested_name && !matchedCategory" class="mt-3 flex items-start gap-2 border border-amber-200 bg-amber-50 p-3 text-xs leading-5 text-amber-900">
                <AlertTriangle class="mt-0.5 size-4 shrink-0" />
                <span>AI could not assign <strong>{{ item.suggested_name }}</strong> to one of your existing categories. Choose one below or leave it Uncategorized.</span>
            </div>
            <form v-if="item.status === 'review' && item.suggested_name" class="mt-4 space-y-3 border-t border-tabarak-line pt-4" @submit.prevent="publish">
                <label class="block">
                    <span class="field-label">Product name</span>
                    <input v-model="form.name" class="field-input" maxlength="180" required />
                    <span v-if="form.errors.name" class="mt-1 block text-xs text-red-600">{{ form.errors.name }}</span>
                </label>
                <label class="block">
                    <span class="field-label">Existing category</span>
                    <SelectMenu v-model="form.category_id" :options="categoryOptions" aria-label="Existing category" />
                    <span v-if="form.errors.category_id" class="mt-1 block text-xs text-red-600">{{ form.errors.category_id }}</span>
                </label>
                <div class="grid grid-cols-[1fr_auto] gap-3">
                    <label class="block">
                        <span class="field-label">Pack quantity</span>
                        <input v-model="form.pack_quantity" class="field-input" min="1" type="number" placeholder="Optional" />
                    </label>
                    <label class="flex items-end gap-2 pb-3 text-xs font-bold text-tabarak-ink">
                        <input v-model="form.allows_open_quantity" type="checkbox" class="rounded border-tabarak-line text-tabarak-blue focus:ring-tabarak-blue" /> Open quantity
                    </label>
                </div>
                <button class="btn-primary w-full" type="submit" :disabled="form.processing">
                    <Send class="size-4" /> {{ form.processing ? 'Publishing…' : 'Publish to shop' }}
                </button>
            </form>
            <Link v-if="item.status === 'approved' && item.approved_product" :href="route('admin.products.edit', item.approved_product.slug)" class="btn-secondary mt-4 w-full">
                Edit published product <ExternalLink class="size-4" />
            </Link>
        </div>
    </article>
</template>
