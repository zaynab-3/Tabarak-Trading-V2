<script setup lang="ts">
import { Check, Plus, ShoppingBag } from '@lucide/vue';
import { onBeforeUnmount, ref } from 'vue';
import axios from 'axios';
import type { Product } from '@/types/catalogue';
import type { CartSummary } from '@/types/orders';
import { useStorefrontCart } from '@/Composables/useStorefrontCart';

const props = withDefaults(defineProps<{ product: Product; quantity?: number; compact?: boolean }>(), {
    quantity: 1,
    compact: false,
});

const { syncCart } = useStorefrontCart();
const processing = ref(false);
const added = ref(false);
const failed = ref(false);
let resetTimer: ReturnType<typeof setTimeout> | undefined;

const add = async () => {
    if (processing.value || !props.product.is_available) return;
    processing.value = true;
    failed.value = false;

    try {
        const response = await axios.post<{ success: boolean; cart: CartSummary }>(
            route('cart.items.store', props.product.slug),
            { quantity: props.quantity },
            { headers: { Accept: 'application/json' } },
        );
        if (response.data.cart) syncCart(response.data.cart);
        added.value = true;
        if (resetTimer) clearTimeout(resetTimer);
        resetTimer = setTimeout(() => { added.value = false; }, 1400);
    } catch {
        failed.value = true;
        if (resetTimer) clearTimeout(resetTimer);
        resetTimer = setTimeout(() => { failed.value = false; }, 2200);
    } finally {
        processing.value = false;
    }
};

onBeforeUnmount(() => { if (resetTimer) clearTimeout(resetTimer); });
</script>

<template>
    <button
        type="button"
        class="inline-flex items-center justify-center font-extrabold transition active:scale-95 disabled:cursor-default"
        :class="compact
            ? 'size-9 shrink-0 rounded-full bg-tabarak-ink text-white hover:bg-tabarak-orange sm:size-10'
            : 'min-h-12 gap-2 rounded-full bg-tabarak-ink px-5 text-sm text-white hover:bg-tabarak-orange'"
        :disabled="processing || !product.is_available"
        :aria-busy="processing"
        :aria-label="failed ? 'Could not add item' : added ? 'Added to cart' : 'Add to cart'"
        :title="failed ? 'Could not add item' : added ? 'Added to cart' : 'Add to cart'"
        @click="add"
    >
        <Check v-if="added" class="size-4" />
        <Plus v-else-if="compact" class="size-4" />
        <ShoppingBag v-else class="size-4" />
        <span v-if="!compact">{{ !product.is_available ? 'Out of stock' : failed ? 'Try again' : added ? 'Added' : 'Add to cart' }}</span>
    </button>
</template>
