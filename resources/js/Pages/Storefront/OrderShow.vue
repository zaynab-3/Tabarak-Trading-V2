<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2, Download, Printer, ShoppingBag } from '@lucide/vue';
import OrderItemsList from '@/Components/Orders/OrderItemsList.vue';
import OrderTotals from '@/Components/Orders/OrderTotals.vue';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { Order } from '@/types/orders';
import { formatDateTime } from '@/Utils/format';

const props = defineProps<{ order: Order }>();
const printInvoice = () => window.open(route('orders.invoice', props.order.public_token), '_blank', 'noopener,noreferrer');
</script>

<template>
    <Head :title="`Order ${order.order_number}`" />
    <StorefrontLayout>
        <div class="page-shell py-8 sm:py-12 lg:py-14">
            <section class="rounded-[28px] bg-[#EDEBE5] p-5 sm:p-7 md:flex md:items-center md:justify-between md:gap-8">
                <div class="flex items-start gap-4">
                    <span class="grid size-12 shrink-0 place-items-center rounded-full bg-white text-emerald-700"><CheckCircle2 class="size-6" /></span>
                    <div>
                        <p class="eyebrow">Order received</p>
                        <h1 class="mt-1 font-display text-3xl font-bold tracking-[-0.04em] text-tabarak-ink sm:text-4xl">{{ order.order_number }}</h1>
                        <p class="mt-2 max-w-xl text-sm leading-6 text-slate-600">Your order has been recorded. Keep this invoice number for your files.</p>
                    </div>
                </div>
                <div class="mt-5 flex flex-wrap gap-2 md:mt-0">
                    <button type="button" class="btn-secondary" @click="printInvoice"><Printer class="size-4" /> Print invoice</button>
                    <a :href="route('orders.invoice', order.public_token)" class="btn-primary"><Download class="size-4" /> Download PDF</a>
                </div>
            </section>

            <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-start xl:gap-12">
                <section>
                    <h2 class="mb-4 font-display text-2xl font-bold tracking-[-0.03em] text-tabarak-ink">Order items</h2>
                    <OrderItemsList :items="order.items || []" />
                </section>
                <aside class="space-y-4 lg:sticky lg:top-24">
                    <section class="rounded-[24px] bg-white p-5 shadow-[0_10px_34px_rgba(25,27,32,0.045)]">
                        <p class="eyebrow">Customer</p>
                        <h2 class="mt-1 text-lg font-extrabold text-tabarak-ink">{{ order.customer_name }}</h2>
                        <p class="mt-2 text-sm font-semibold text-slate-500">{{ order.customer_phone }}</p>
                        <p v-if="order.billing_address" class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-500">{{ order.billing_address }}</p>
                        <dl v-if="order.purchase_order_number || order.delivery_date || order.payment_method" class="mt-5 space-y-2 text-sm">
                            <div v-if="order.purchase_order_number" class="flex justify-between gap-3"><dt class="text-slate-400">PO #</dt><dd class="font-bold">{{ order.purchase_order_number }}</dd></div>
                            <div v-if="order.delivery_date" class="flex justify-between gap-3"><dt class="text-slate-400">Delivery</dt><dd class="font-bold">{{ order.delivery_date }}</dd></div>
                            <div v-if="order.payment_method" class="flex justify-between gap-3"><dt class="text-slate-400">Payment</dt><dd class="font-bold capitalize">{{ order.payment_method }}</dd></div>
                        </dl>
                        <p class="mt-5 text-xs font-semibold text-slate-400">Submitted {{ formatDateTime(order.submitted_at) }}</p>
                    </section>
                    <OrderTotals :subtotal="order.subtotal" :total="order.total" />
                    <Link :href="route('shop')" class="btn-secondary w-full"><ShoppingBag class="size-4" /> Return to shop</Link>
                </aside>
            </div>
        </div>
    </StorefrontLayout>
</template>
