<script setup lang="ts">
import { Check, Minus, Plus, ShoppingBag } from '@lucide/vue';
import axios from 'axios';
import { computed, onBeforeUnmount, ref } from 'vue';
import type { Product } from '@/types/catalogue';
import type { CartSummary } from '@/types/orders';
import { formatMoney } from '@/Utils/format';
import { useStorefrontCart } from '@/Composables/useStorefrontCart';

const props = defineProps<{ product: Product }>();
const { syncCart } = useStorefrontCart();
const quantity = ref(1);
const processing = ref(false);
const added = ref(false);
const error = ref('');
let resetTimer: ReturnType<typeof setTimeout> | undefined;

const maximumQuantity = computed(() => props.product.tracks_stock ? Math.max(1, props.product.stock_quantity ?? 0) : 999);

const changeQuantity = (amount: number) => {
    quantity.value = Math.max(1, Math.min(maximumQuantity.value, quantity.value + amount));
};

const add = async () => {
    if (processing.value || !props.product.is_available) return;
    processing.value = true;
    error.value = '';

    try {
        const response = await axios.post<{ success: boolean; cart: CartSummary }>(
            route('cart.items.store', props.product.slug),
            { quantity: quantity.value },
            { headers: { Accept: 'application/json' } },
        );
        if (response.data.cart) syncCart(response.data.cart);
        added.value = true;
        quantity.value = 1;
        if (resetTimer) clearTimeout(resetTimer);
        resetTimer = setTimeout(() => { added.value = false; }, 1500);
    } catch (err: any) {
        error.value = err.response?.data?.message || Object.values(err.response?.data?.errors || {}).flat().join(' ') || 'Could not add this item right now.';
    } finally {
        processing.value = false;
    }
};

onBeforeUnmount(() => { if (resetTimer) clearTimeout(resetTimer); });
</script>

<template>
    <section v-if="product.unit_price" class="mt-8 rounded-[24px] bg-[#EFEEE9] p-5 sm:p-6"
        aria-labelledby="order-product">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p id="order-product"
                    class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-tabarak-orange">USD price</p>
                <p class="mt-1 font-display text-3xl font-bold tracking-[-0.04em] text-tabarak-ink sm:text-4xl">{{
                    formatMoney(product.unit_price) }}</p>
                <p class="mt-1 text-xs font-semibold text-slate-400">Per {{ product.unit_label || 'item' }}</p>
            </div>

            <form v-if="product.is_available" class="flex items-center gap-3" @submit.prevent="add"
                style="justify-content: space-between;">
                <div class="inline-flex h-12 items-center rounded-full bg-white px-1">
                    <button type="button"
                        class="grid size-10 place-items-center rounded-full text-slate-500 transition hover:bg-[#F0EFEA] hover:text-tabarak-ink disabled:opacity-30"
                        :disabled="quantity <= 1" aria-label="Decrease quantity" @click="changeQuantity(-1)">
                        <Minus class="size-4" />
                    </button>
                    <input v-model.number="quantity"
                        class="h-10 w-12 border-0 bg-transparent p-0 text-center text-sm font-extrabold text-tabarak-ink focus:ring-0 [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:m-0 [&::-webkit-outer-spin-button]:m-0"
                        type="number" min="1" :max="maximumQuantity" required />
                    <button type="button"
                        class="grid size-10 place-items-center rounded-full text-slate-500 transition hover:bg-[#F0EFEA] hover:text-tabarak-ink disabled:opacity-30"
                        :disabled="quantity >= maximumQuantity" aria-label="Increase quantity"
                        @click="changeQuantity(1)">
                        <Plus class="size-4" />
                    </button>
                </div>

                <button
                    class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-tabarak-ink px-5 text-sm font-extrabold text-white transition hover:bg-tabarak-orange active:scale-[0.99] disabled:cursor-default disabled:opacity-60"
                    :disabled="processing" type="submit" :aria-busy="processing">
                    <Check v-if="added" class="size-4" />
                    <ShoppingBag v-else class="size-4" />
                    {{ added ? 'Added' : 'Add to cart' }}
                </button>
            </form>
            <p v-else class="text-sm font-extrabold text-red-700">Out of stock</p>
        </div>
        <p v-if="product.tracks_stock && product.is_available" class="mt-3 text-xs font-semibold text-slate-400">{{
            product.stock_quantity }} available</p>
        <p v-if="error" class="mt-3 text-sm font-bold text-red-600">{{ error }}</p>
    </section>
</template>
