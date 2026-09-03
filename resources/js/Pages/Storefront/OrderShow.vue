<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2, Download, MapPin, Printer, ShoppingBag } from '@lucide/vue';
import OrderItemsList from '@/Components/Orders/OrderItemsList.vue';
import OrderTotals from '@/Components/Orders/OrderTotals.vue';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { Order } from '@/types/orders';
import { formatDateTime } from '@/Utils/format';

defineProps<{ order: Order }>();
const printInvoice = () => window.print();
</script>

<template>
    <Head :title="`Order ${order.order_number}`" />
    <StorefrontLayout>
        <div class="page-shell py-6 md:py-10">
            <section class="rounded-lg border border-tabarak-line bg-tabarak-mist p-5 md:flex md:items-center md:justify-between md:gap-6 md:p-7">
                <div class="flex items-start gap-4"><CheckCircle2 class="mt-1 size-7 shrink-0 text-tabarak-blue" /><div><p class="eyebrow">Order received</p><h1 class="mt-1 font-display text-3xl font-bold text-tabarak-ink">{{ order.order_number }}</h1><p class="mt-2 text-sm leading-6 text-slate-600">Tabarak Trading received the order and will call {{ order.customer_phone }}.</p></div></div>
                <div class="mt-5 flex flex-wrap gap-2 md:mt-0">
                    <button type="button" class="btn-secondary" @click="printInvoice"><Printer class="size-4" /> Print</button>
                    <a :href="route('orders.invoice', order.public_token)" class="btn-primary"><Download class="size-4" /> Download PDF</a>
                </div>
            </section>

            <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-start">
                <section><h2 class="mb-3 font-display text-2xl font-bold text-tabarak-ink">Full item list</h2><OrderItemsList :items="order.items || []" /></section>
                <aside class="space-y-4 lg:sticky lg:top-24">
                    <section class="rounded-lg border border-tabarak-line bg-white p-5">
                        <p class="eyebrow">Customer</p>
                        <h2 class="mt-1 text-lg font-bold text-tabarak-ink">{{ order.customer_name }}</h2>
                        <p class="mt-2 text-sm text-slate-600">{{ order.customer_phone }}</p>
                        <p v-if="order.customer_address" class="mt-2 flex items-start gap-1.5 text-xs text-slate-600">
                            <MapPin class="size-3.5 shrink-0 mt-0.5 text-slate-400" />
                            <span>{{ order.customer_address }}</span>
                        </p>
                        <p class="mt-4 text-xs text-slate-400">Submitted {{ formatDateTime(order.submitted_at) }}</p>
                    </section>
                    <OrderTotals :subtotal="order.subtotal" :total="order.total" />
                    <Link :href="route('shop')" class="btn-secondary w-full"><ShoppingBag class="size-4" /> Return to shop</Link>
                </aside>
            </div>
        </div>
    </StorefrontLayout>
</template>

<style scoped>
@media print {
    :global(header), :global(footer), button, a { display: none !important; }
    :global(body) { background: white !important; }
}
</style>
