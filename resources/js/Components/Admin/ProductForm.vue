<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { Save } from '@lucide/vue';
import type { Product, TaxonomyRef } from '@/types/catalogue';

const props = defineProps<{ product?: Product; categories: TaxonomyRef[]; brands: TaxonomyRef[]; statuses: string[] }>();
const form = useForm({
    name: props.product?.name ?? '',
    sku: props.product?.sku ?? '',
    category_id: props.product?.category?.id ?? '',
    brand_id: props.product?.brand?.id ?? '',
    short_description: props.product?.short_description ?? '',
    description: props.product?.description ?? '',
    weight_value: props.product?.weight_value ?? '',
    weight_unit: props.product?.weight_unit ?? '',
    pack_quantity: props.product?.pack_quantity ?? '',
    allows_open_quantity: props.product?.allows_open_quantity ?? false,
    unit_label: props.product?.unit_label ?? 'case',
    status: props.product?.status ?? 'draft',
    is_featured: props.product?.is_featured ?? false,
});

const submit = () => {
    if (props.product) form.put(route('admin.products.update', props.product.slug));
    else form.post(route('admin.products.store'));
};
</script>

<template>
    <form class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_320px]" @submit.prevent="submit">
        <section class="surface space-y-5 p-5 md:p-6">
            <div class="border-b border-tabarak-line pb-4">
                <p class="eyebrow">Product information</p>
                <h2 class="mt-1 font-display text-2xl font-bold text-tabarak-ink">Core details</h2>
                <p class="mt-1 text-sm text-slate-500">The information customers use to identify this product.</p>
            </div>
            <div class="grid gap-5 md:grid-cols-2">
                <label class="block md:col-span-2"><span class="field-label">Product name *</span><input v-model="form.name" class="field-input" maxlength="180" required /><span v-if="form.errors.name" class="mt-1 block text-xs text-red-600">{{ form.errors.name }}</span></label>
                <label class="block"><span class="field-label">SKU</span><input v-model="form.sku" class="field-input" maxlength="100" placeholder="TT-0001" /><span v-if="form.errors.sku" class="mt-1 block text-xs text-red-600">{{ form.errors.sku }}</span></label>
                <label class="block"><span class="field-label">Category</span><select v-model="form.category_id" class="field-input"><option value="">Uncategorized</option><option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option></select></label>
                <label class="block"><span class="field-label">Brand</span><select v-model="form.brand_id" class="field-input"><option value="">No brand</option><option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option></select></label>
                <label class="block"><span class="field-label">Status</span><select v-model="form.status" class="field-input"><option v-for="status in statuses" :key="status" :value="status" class="capitalize">{{ status }}</option></select></label>
                <label class="block md:col-span-2"><span class="field-label">Short description</span><textarea v-model="form.short_description" class="field-input min-h-24" maxlength="500" placeholder="A concise catalogue summary." /></label>
                <label class="block md:col-span-2"><span class="field-label">Full description</span><textarea v-model="form.description" class="field-input min-h-40" maxlength="10000" placeholder="Product details, packaging and useful buyer information." /></label>
            </div>
        </section>

        <aside class="h-fit space-y-5 xl:sticky xl:top-24">
            <section class="surface p-5">
                <p class="eyebrow">Ordering</p>
                <h2 class="mt-1 font-display text-xl font-bold text-tabarak-ink">Pack details</h2>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <label><span class="field-label">Weight</span><input v-model="form.weight_value" class="field-input" min="0" step="0.01" type="number" /></label>
                    <label><span class="field-label">Unit</span><select v-model="form.weight_unit" class="field-input"><option value="">—</option><option value="g">g</option><option value="kg">kg</option><option value="ml">ml</option><option value="l">l</option><option value="oz">oz</option></select></label>
                    <label><span class="field-label">Pack quantity</span><input v-model="form.pack_quantity" class="field-input" min="1" type="number" /></label>
                    <label><span class="field-label">Pack label</span><input v-model="form.unit_label" class="field-input" placeholder="case" /></label>
                </div>
                <label class="mt-4 flex min-h-11 items-start gap-3 border-t border-tabarak-line pt-4">
                    <input v-model="form.allows_open_quantity" type="checkbox" class="mt-0.5 rounded border-tabarak-line text-tabarak-blue focus:ring-tabarak-blue" />
                    <span><span class="block text-sm font-bold text-tabarak-ink">Open quantity</span><span class="mt-1 block text-xs leading-5 text-slate-500">Allow customers to request any quantity instead of a full case only.</span></span>
                </label>
            </section>
            <section class="surface p-5"><label class="flex min-h-11 items-center gap-3"><input v-model="form.is_featured" type="checkbox" class="rounded border-tabarak-line text-tabarak-blue focus:ring-tabarak-blue" /><span><span class="block text-sm font-bold text-tabarak-ink">Featured product</span><span class="block text-xs text-slate-500">Show prominently on the storefront.</span></span></label></section>
            <div class="flex gap-2"><Link :href="route('admin.products.index')" class="btn-secondary flex-1">Cancel</Link><button class="btn-primary flex-1" type="submit" :disabled="form.processing"><Save class="size-4" /> {{ form.processing ? 'Saving…' : 'Save' }}</button></div>
        </aside>
    </form>
</template>
