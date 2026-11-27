<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ShoppingBag } from '@lucide/vue';
import { ref, watch } from 'vue';
import CartLineItem from '@/Components/Storefront/CartLineItem.vue';
import CheckoutCustomerForm from '@/Components/Storefront/CheckoutCustomerForm.vue';
import OrderTotals from '@/Components/Orders/OrderTotals.vue';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { CartItem, CartSummary } from '@/types/orders';
import { useStorefrontCart } from '@/Composables/useStorefrontCart';

const props = defineProps<{ cart: CartSummary }>();
const checkoutOpen = ref(false);
const localCart = ref<CartSummary>({ ...props.cart, items: props.cart.items.map((item) => ({ ...item })) });
const { syncCart } = useStorefrontCart();

watch(() => props.cart, (cart) => {
    localCart.value = { ...cart, items: cart.items.map((item) => ({ ...item })) };
}, { deep: true });

const recalcOptimistic = () => {
    const subtotal = localCart.value.items.reduce((sum, item) => sum + Math.round(Number(item.line_total) * 100), 0);
    localCart.value.subtotal = (subtotal / 100).toFixed(2);
    localCart.value.item_count = localCart.value.items.reduce((sum, item) => sum + item.quantity, 0);
    syncCart(localCart.value);
};

const handleItemUpdate = (updated: CartItem) => {
    const index = localCart.value.items.findIndex((item) => item.product.id === updated.product.id);
    if (index >= 0) localCart.value.items[index] = { ...updated };
    recalcOptimistic();
};

const handleSynced = (cart: CartSummary) => {
    localCart.value = { ...cart, items: cart.items.map((item) => ({ ...item })) };
    syncCart(cart);
};

const handleRemove = (productId: number) => {
    localCart.value.items = localCart.value.items.filter((item) => item.product.id !== productId);
    recalcOptimistic();
};
</script>

<template>
    <Head title="Cart" />
    <StorefrontLayout>
        <div class="page-shell py-6 sm:py-9 lg:py-11">
            <div class="flex items-start gap-3 sm:gap-4">
                <Link
                    :href="route('shop')"
                    class="mt-0.5 grid size-11 shrink-0 place-items-center rounded-full bg-white text-tabarak-ink transition hover:bg-[#ECEAE5]"
                    aria-label="Back to catalogue"
                    title="Back to catalogue"
                >
                    <ArrowLeft class="size-[18px]" />
                </Link>
                <div class="min-w-0">
                    <p class="eyebrow">Your order</p>
                    <h1 class="mt-1 font-display text-[clamp(2rem,5vw,3.35rem)] font-bold tracking-[-0.05em] text-tabarak-ink">Shopping cart</h1>
                    <p class="mt-1.5 text-sm font-semibold text-slate-400">{{ localCart.item_count }} item{{ localCart.item_count === 1 ? '' : 's' }} · USD pricing</p>
                </div>
            </div>

            <div v-if="localCart.items.length" class="mt-7 grid gap-7 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start xl:gap-10">
                <section class="grid gap-3" aria-label="Cart items">
                    <CartLineItem
                        v-for="item in localCart.items"
                        :key="item.product.id"
                        :item="item"
                        @update:item="handleItemUpdate"
                        @synced="handleSynced"
                        @remove="handleRemove"
                    />
                </section>

                <aside class="space-y-3 lg:sticky lg:top-24">
                    <OrderTotals :subtotal="localCart.subtotal" :total="localCart.subtotal" />
                    <button type="button" class="btn-primary w-full" @click="checkoutOpen = true">Checkout</button>
                    <p class="px-3 text-center text-[11px] font-semibold leading-5 text-slate-400">Customer, billing and delivery details are completed at checkout.</p>
                </aside>
            </div>

            <section v-else class="mx-auto my-20 max-w-lg text-center">
                <span class="mx-auto grid size-16 place-items-center rounded-full bg-[#ECEAE5] text-tabarak-ink"><ShoppingBag class="size-7" /></span>
                <h2 class="mt-5 font-display text-2xl font-bold tracking-[-0.03em] text-tabarak-ink">Your cart is empty</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Browse the catalogue and add products to start an order.</p>
                <Link :href="route('shop')" class="btn-primary mt-6">Browse products</Link>
            </section>
        </div>

        <CheckoutCustomerForm :open="checkoutOpen" :total="localCart.subtotal" :item-count="localCart.item_count" @close="checkoutOpen = false" />
    </StorefrontLayout>
</template>
