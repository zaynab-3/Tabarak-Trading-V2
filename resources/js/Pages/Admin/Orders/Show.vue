<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Check, Download, MapPin, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import DeleteOrderDialog from '@/Components/Admin/DeleteOrderDialog.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import StatusBadge from '@/Components/Admin/StatusBadge.vue';
import OrderItemsList from '@/Components/Orders/OrderItemsList.vue';
import OrderTotals from '@/Components/Orders/OrderTotals.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { Order } from '@/types/orders';
import { formatDateTime } from '@/Utils/format';

const props = defineProps<{ order: Order }>();
const confirmingDelete = ref(false);
const complete = () => router.patch(route('admin.orders.complete', props.order.public_token));
</script>

<template>
    <Head :title="order.order_number" />
    <AdminLayout>
        <PageHeader eyebrow="Order details" :title="order.order_number" :description="`Submitted ${formatDateTime(order.submitted_at)}`">
            <Link :href="route('admin.orders.index')" class="btn-secondary"><ArrowLeft class="size-4" /> All orders</Link>
            <a :href="route('orders.invoice', order.public_token)" class="btn-secondary"><Download class="size-4" /> PDF</a>
            <button v-if="order.status !== 'completed'" class="btn-primary" type="button" @click="complete"><Check class="size-4" /> Mark completed</button>
            <button class="btn-danger" type="button" @click="confirmingDelete = true"><Trash2 class="size-4" /> Delete</button>
        </PageHeader>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px] xl:items-start">
            <section><div class="mb-3 flex items-center justify-between"><h2 class="admin-section-title">Full item list</h2><StatusBadge :status="order.status" /></div><OrderItemsList :items="order.items || []" /></section>
            <aside class="space-y-4 xl:sticky xl:top-24">
                <section class="surface p-5">
                    <p class="eyebrow">Customer</p>
                    <h2 class="mt-1 text-xl font-bold text-tabarak-ink">{{ order.customer_name }}</h2>
                    <a :href="`tel:${order.customer_phone}`" class="mt-2 inline-block text-sm font-bold text-tabarak-blue">{{ order.customer_phone }}</a>
                    <p v-if="order.customer_address" class="mt-2 flex items-start gap-1.5 text-xs text-slate-600">
                        <MapPin class="size-3.5 shrink-0 mt-0.5 text-slate-400" />
                        <span>{{ order.customer_address }}</span>
                    </p>
                    <dl class="mt-5 space-y-3 border-t border-tabarak-line pt-4 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Submitted</dt><dd class="text-right font-semibold">{{ formatDateTime(order.submitted_at) }}</dd></div>
                        <div v-if="order.completed_at" class="flex justify-between gap-3"><dt class="text-slate-500">Completed</dt><dd class="text-right font-semibold">{{ formatDateTime(order.completed_at) }}</dd></div>
                    </dl>
                </section>
                <OrderTotals :subtotal="order.subtotal" :total="order.total" />
            </aside>
        </div>
        <DeleteOrderDialog :open="confirmingDelete" :order="order" @close="confirmingDelete = false" @finished="confirmingDelete = false" />
    </AdminLayout>
</template>
