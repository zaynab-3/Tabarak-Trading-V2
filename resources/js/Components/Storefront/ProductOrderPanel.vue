<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ShoppingCart } from '@lucide/vue';
import type { Product } from '@/types/catalogue';
import { formatMoney } from '@/Utils/format';
import { computed } from 'vue';

const props = defineProps<{ product: Product }>();

const form = useForm({
    quantity: 1,
});
const cartError = computed(() => (form.errors as Record<string, string>).cart);
const maximumQuantity = computed(() => props.product.tracks_stock ? Math.max(1, props.product.stock_quantity ?? 0) : 999);

const add = () => form.post(route('cart.items.store', props.product.slug), {
    preserveScroll: true,
    onSuccess: () => {
        form.reset('quantity');
    },
});
</script>

<template>
    <section v-if="product.unit_price" class="mt-7 rounded-lg border border-tabarak-line bg-tabarak-mist p-4 sm:p-5"
        aria-labelledby="order-product">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p id="order-product" class="text-xs font-bold uppercase tracking-[0.16em] text-tabarak-orange">USD
                    price</p>
                <p class="mt-1 font-display text-3xl font-bold text-tabarak-ink">{{ formatMoney(product.unit_price) }}
                </p>
                <p class="mt-1 text-xs text-slate-500">Per {{ product.unit_label || 'item' }}</p>
            </div>
            <form v-if="product.is_available" class="flex flex-wrap items-end gap-2" @submit.prevent="add">
                <label>
                    <span class="field-label">Quantity</span>
                    <input v-model.number="form.quantity" class="field-input w-24" type="number" min="1"
                        :max="maximumQuantity" required />
                </label>
                <button
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md bg-tabarak-orange px-5 text-sm font-bold text-white transition hover:bg-[#E94E00] disabled:opacity-60"
                    :disabled="form.processing" type="submit">
                    <ShoppingCart class="size-4" /> {{ form.processing ? 'Adding…' : '' }}
                </button>
            </form>
            <p v-else class="rounded-md bg-red-50 px-4 py-3 text-sm font-bold text-red-700">Out of stock</p>
        </div>

        <p v-if="product.tracks_stock && product.is_available" class="mt-3 text-xs font-semibold text-slate-500">{{
            product.stock_quantity }} available</p>
        <p v-if="form.errors.quantity || cartError" class="mt-3 text-sm font-semibold text-red-600">{{
            form.errors.quantity || cartError }}</p>
    </section>
</template>
