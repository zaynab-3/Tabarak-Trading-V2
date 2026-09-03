<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft, ShieldCheck, ShoppingCart } from '@lucide/vue';
import CartLineItem from '@/Components/Storefront/CartLineItem.vue';
import CheckoutCustomerForm from '@/Components/Storefront/CheckoutCustomerForm.vue';
import OrderTotals from '@/Components/Orders/OrderTotals.vue';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { PageProps } from '@/types';
import type { CartItem, CartSummary } from '@/types/orders';
import { computed, ref, watch } from 'vue';

const props = defineProps<{ cart: CartSummary }>();
const page = usePage<PageProps>();
const isAdmin = computed(() => Boolean(page.props.auth?.user?.is_admin));

const cartData = ref<CartSummary>({
    items: [...props.cart.items],
    item_count: props.cart.item_count,
    subtotal: props.cart.subtotal,
    has_custom_prices: props.cart.has_custom_prices,
    currency: props.cart.currency,
});

watch(
    () => props.cart,
    (newCart) => {
        cartData.value = {
            items: [...newCart.items],
            item_count: newCart.item_count,
            subtotal: newCart.subtotal,
            has_custom_prices: newCart.has_custom_prices,
            currency: newCart.currency,
        };
    },
    { deep: true },
);

const recalculateTotals = () => {
    let totalCents = 0;
    let totalCount = 0;
    let hasCustom = false;

    for (const line of cartData.value.items) {
        const unitCents = Math.round(Number(line.unit_price) * 100);
        const lineCents = unitCents * line.quantity;
        line.line_total = (lineCents / 100).toFixed(2);
        totalCents += lineCents;
        totalCount += line.quantity;
        if (line.is_custom_price) {
            hasCustom = true;
        }
    }

    cartData.value.subtotal = (totalCents / 100).toFixed(2);
    cartData.value.item_count = totalCount;
    cartData.value.has_custom_prices = hasCustom;
    page.props.cart = { ...cartData.value };
};

const handleItemUpdate = (index: number, updatedItem: CartItem) => {
    cartData.value.items[index] = updatedItem;
    recalculateTotals();
};

const handleCartSynced = (serverCart: CartSummary) => {
    cartData.value = {
        items: [...serverCart.items],
        item_count: serverCart.item_count,
        subtotal: serverCart.subtotal,
        has_custom_prices: serverCart.has_custom_prices,
        currency: serverCart.currency,
    };
    page.props.cart = { ...cartData.value };
};

const handleItemRemove = (productId: number) => {
    cartData.value.items = cartData.value.items.filter((item) => item.product.id !== productId);
    recalculateTotals();
};
</script>

<template>
    <Head title="Cart" />
    <StorefrontLayout>
        <div class="page-shell py-5 md:py-8">
            <div class="flex flex-wrap items-end justify-between gap-4 border-b border-tabarak-line pb-5">
                <div>
                    <p class="eyebrow">Your order</p>
                    <h1 class="mt-1 font-display text-3xl font-bold text-tabarak-ink md:text-4xl">Shopping cart</h1>
                    <p class="mt-2 text-sm text-slate-500">{{ cartData.item_count }} item{{ cartData.item_count === 1 ? '' : 's' }} · USD pricing</p>
                </div>
                <Link :href="route('shop')" class="btn-secondary"><ArrowLeft class="size-4" /> Continue shopping</Link>
            </div>

            <div v-if="isAdmin && cartData.has_custom_prices" class="mt-4 flex items-center gap-2.5 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                <ShieldCheck class="size-5 shrink-0 text-amber-600" />
                <p>
                    <span class="font-bold">Custom rates active:</span> One or more items in this cart have custom rates calculated by admin.
                </p>
            </div>

            <div v-if="cartData.items.length" class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-start">
                <section class="space-y-4" aria-label="Cart items">
                    <CartLineItem
                        v-for="(item, index) in cartData.items"
                        :key="item.product.id"
                        :item="item"
                        @update:item="(updated) => handleItemUpdate(index, updated)"
                        @synced="handleCartSynced"
                        @remove="handleItemRemove"
                    />
                </section>
                <aside class="space-y-4 lg:sticky lg:top-24">
                    <OrderTotals :subtotal="cartData.subtotal" :total="cartData.subtotal" />
                    <CheckoutCustomerForm />
                </aside>
            </div>

            <section v-else class="mx-auto my-16 max-w-lg rounded-lg border border-tabarak-line bg-tabarak-mist p-8 text-center">
                <ShoppingCart class="mx-auto size-10 text-tabarak-blue" />
                <h2 class="mt-4 font-display text-2xl font-bold text-tabarak-ink">Your cart is empty</h2>
                <p class="mt-2 text-sm text-slate-500">Browse the catalogue and add priced products to start an order.</p>
                <Link :href="route('shop')" class="btn-primary mt-5">Browse products</Link>
            </section>
        </div>
    </StorefrontLayout>
</template>
