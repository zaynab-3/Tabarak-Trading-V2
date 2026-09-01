<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Image as ImageIcon, Trash2 } from '@lucide/vue';
import type { CartItem } from '@/types/orders';
import { formatMoney, productPackLabel } from '@/Utils/format';
import { computed } from 'vue';

const props = defineProps<{ item: CartItem }>();
const form = useForm({ quantity: props.item.quantity });
const cartError = computed(() => (form.errors as Record<string, string>).cart);

const update = () => form.patch(route('cart.items.update', props.item.product.slug), { preserveScroll: true });
const remove = () => router.delete(route('cart.items.destroy', props.item.product.slug), { preserveScroll: true });
</script>

<template>
    <article class="grid gap-4 rounded-lg border border-tabarak-line bg-white p-4 sm:grid-cols-[132px_minmax(0,1fr)] sm:p-5">
        <div class="grid min-h-32 place-items-center overflow-hidden rounded-md bg-tabarak-mist p-3">
            <img v-if="item.product.primary_image" :src="item.product.primary_image.url" :alt="item.product.primary_image.alt_text || item.product.name" class="h-28 w-full object-contain" />
            <ImageIcon v-else class="size-8 text-slate-300" />
        </div>
        <div class="min-w-0">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-tabarak-orange">{{ item.product.brand?.name || 'Tabarak selection' }}</p>
                    <h2 class="mt-1 font-display text-xl font-bold leading-tight text-tabarak-ink">{{ item.product.name }}</h2>
                    <p class="mt-2 text-sm text-slate-500">{{ productPackLabel(item.product) }}</p>
                </div>
                <button type="button" class="grid size-10 shrink-0 place-items-center rounded-md border border-red-200 text-red-600 transition hover:bg-red-50" aria-label="Remove item" @click="remove"><Trash2 class="size-4" /></button>
            </div>
            <div class="mt-5 flex flex-wrap items-end justify-between gap-4 border-t border-tabarak-line pt-4">
                <form class="flex items-end gap-2" @submit.prevent="update">
                    <label><span class="field-label">Quantity</span><input v-model.number="form.quantity" class="field-input w-24" type="number" min="1" max="999" required /></label>
                    <button class="btn-secondary px-4" type="submit" :disabled="form.processing || form.quantity === item.quantity">Update</button>
                </form>
                <div class="text-right">
                    <p class="text-xs text-slate-500">{{ formatMoney(item.unit_price) }} each</p>
                    <p class="mt-1 text-xl font-bold text-tabarak-blue">{{ formatMoney(item.line_total) }}</p>
                </div>
            </div>
            <p v-if="form.errors.quantity || cartError" class="mt-2 text-sm text-red-600">{{ form.errors.quantity || cartError }}</p>
        </div>
    </article>
</template>
