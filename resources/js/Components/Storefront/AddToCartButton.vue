<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ShoppingCart } from '@lucide/vue';
import { ref } from 'vue';
import type { Product } from '@/types/catalogue';

const props = withDefaults(defineProps<{ product: Product; quantity?: number; compact?: boolean }>(), {
    quantity: 1,
    compact: false,
});

const processing = ref(false);
const add = () => {
    processing.value = true;
    router.post(route('cart.items.store', props.product.slug), { quantity: props.quantity }, {
        preserveScroll: true,
        onFinish: () => { processing.value = false; },
    });
};
</script>

<template>
    <button
        type="button"
        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md bg-tabarak-orange px-4 text-sm font-bold text-white transition hover:bg-[#E94E00] disabled:cursor-not-allowed disabled:opacity-60"
        :class="compact ? 'px-3' : 'px-5'"
        :disabled="processing || !product.is_available"
        @click="add"
    >
        <ShoppingCart class="size-4" />
        {{ !product.is_available ? 'Out of stock' : processing ? 'Adding…' : 'Add to cart' }}
    </button>
</template>
