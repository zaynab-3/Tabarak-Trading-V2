<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ShoppingCart } from '@lucide/vue';
import CartLineItem from '@/Components/Storefront/CartLineItem.vue';
import CheckoutCustomerForm from '@/Components/Storefront/CheckoutCustomerForm.vue';
import OrderTotals from '@/Components/Orders/OrderTotals.vue';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { CartSummary } from '@/types/orders';

defineProps<{ cart: CartSummary }>();
</script>

<template>
    <Head title="Cart" />
    <StorefrontLayout>
        <div class="page-shell py-5 md:py-8">
            <div class="flex flex-wrap items-end justify-between gap-4 border-b border-tabarak-line pb-5">
                <div><p class="eyebrow">Your order</p><h1 class="mt-1 font-display text-3xl font-bold text-tabarak-ink md:text-4xl">Shopping cart</h1><p class="mt-2 text-sm text-slate-500">{{ cart.item_count }} item{{ cart.item_count === 1 ? '' : 's' }} · USD pricing</p></div>
                <Link :href="route('shop')" class="btn-secondary"><ArrowLeft class="size-4" /> Continue shopping</Link>
            </div>

            <div v-if="cart.items.length" class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-start">
                <section class="space-y-4" aria-label="Cart items"><CartLineItem v-for="item in cart.items" :key="item.product.id" :item="item" /></section>
                <aside class="space-y-4 lg:sticky lg:top-24"><OrderTotals :subtotal="cart.subtotal" :total="cart.subtotal" /><CheckoutCustomerForm /></aside>
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
