<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Check, Download, Save, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import DeleteOrderDialog from '@/Components/Admin/DeleteOrderDialog.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import StatusBadge from '@/Components/Admin/StatusBadge.vue';
import OrderItemsList from '@/Components/Orders/OrderItemsList.vue';
import OrderTotals from '@/Components/Orders/OrderTotals.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { Order, OrderPaymentMethod } from '@/types/orders';
import { formatDateTime } from '@/Utils/format';

const props = defineProps<{ order: Order }>();
const confirmingDelete = ref(false);
const complete = () => router.patch(route('admin.orders.complete', props.order.public_token));

const invoiceForm = useForm<{
    purchase_order_number: string;
    billing_address: string;
    shipping_address: string;
    delivery_date: string;
    payment_method: '' | OrderPaymentMethod;
    check_number: string;
}>({
    purchase_order_number: props.order.purchase_order_number ?? '',
    billing_address: props.order.billing_address ?? '',
    shipping_address: props.order.shipping_address ?? '',
    delivery_date: props.order.delivery_date ?? '',
    payment_method: props.order.payment_method ?? '',
    check_number: props.order.check_number ?? '',
});

const saveInvoiceDetails = () => {
    invoiceForm.patch(route('admin.orders.invoice-details.update', props.order.public_token), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="order.order_number" />
    <AdminLayout>
        <PageHeader eyebrow="Order details" :title="order.order_number" :description="`Submitted ${formatDateTime(order.submitted_at)}`">
            <Link :href="route('admin.orders.index')" class="btn-secondary"><ArrowLeft class="size-4" /> All orders</Link>
            <a :href="route('orders.invoice', order.public_token)" class="btn-secondary"><Download class="size-4" /> PDF invoice</a>
            <button v-if="order.status !== 'completed'" class="btn-primary" type="button" @click="complete"><Check class="size-4" /> Mark completed</button>
            <button class="btn-danger" type="button" @click="confirmingDelete = true"><Trash2 class="size-4" /> Delete</button>
        </PageHeader>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px] xl:items-start">
            <section>
                <div class="mb-3 flex items-center justify-between"><h2 class="admin-section-title">Full item list</h2><StatusBadge :status="order.status" /></div>
                <OrderItemsList :items="order.items || []" />
            </section>

            <aside class="space-y-4 xl:sticky xl:top-24">
                <section class="surface p-5">
                    <p class="eyebrow">Customer</p>
                    <h2 class="mt-1 text-xl font-bold text-tabarak-ink">{{ order.customer_name }}</h2>
                    <a :href="`tel:${order.customer_phone}`" class="mt-2 inline-block text-sm font-bold text-tabarak-blue">{{ order.customer_phone }}</a>
                    <p v-if="order.billing_address" class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600">{{ order.billing_address }}</p>
                    <dl class="mt-5 space-y-3 border-t border-tabarak-line pt-4 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Invoice #</dt><dd class="text-right font-semibold">{{ order.order_number }}</dd></div>
                        <div v-if="order.purchase_order_number" class="flex justify-between gap-3"><dt class="text-slate-500">PO #</dt><dd class="text-right font-semibold">{{ order.purchase_order_number }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Submitted</dt><dd class="text-right font-semibold">{{ formatDateTime(order.submitted_at) }}</dd></div>
                        <div v-if="order.completed_at" class="flex justify-between gap-3"><dt class="text-slate-500">Completed</dt><dd class="text-right font-semibold">{{ formatDateTime(order.completed_at) }}</dd></div>
                    </dl>
                </section>

                <section class="surface p-5">
                    <p class="eyebrow">Printed invoice</p>
                    <h2 class="mt-1 font-display text-xl font-bold text-tabarak-ink">Invoice details</h2>
                    <p class="mt-1 text-xs leading-5 text-slate-500">These fields appear on the downloadable receipt. Print date is added automatically when the PDF is generated.</p>

                    <form class="mt-5 space-y-4" @submit.prevent="saveInvoiceDetails">
                        <label class="block">
                            <span class="field-label">Purchase order #</span>
                            <input v-model.trim="invoiceForm.purchase_order_number" class="field-input" maxlength="80" placeholder="Optional PO number" />
                            <span v-if="invoiceForm.errors.purchase_order_number" class="mt-1 block text-xs font-semibold text-red-600">{{ invoiceForm.errors.purchase_order_number }}</span>
                        </label>

                        <label class="block">
                            <span class="field-label">Delivery date</span>
                            <input v-model="invoiceForm.delivery_date" class="field-input" type="date" />
                            <span v-if="invoiceForm.errors.delivery_date" class="mt-1 block text-xs font-semibold text-red-600">{{ invoiceForm.errors.delivery_date }}</span>
                        </label>

                        <label class="block">
                            <span class="field-label">Payment method</span>
                            <select v-model="invoiceForm.payment_method" class="field-input">
                                <option value="">Not recorded</option>
                                <option value="cash">Cash</option>
                                <option value="check">Check</option>
                            </select>
                            <span v-if="invoiceForm.errors.payment_method" class="mt-1 block text-xs font-semibold text-red-600">{{ invoiceForm.errors.payment_method }}</span>
                        </label>

                        <label v-if="invoiceForm.payment_method === 'check'" class="block">
                            <span class="field-label">Check #</span>
                            <input v-model.trim="invoiceForm.check_number" class="field-input" maxlength="80" placeholder="Optional check number" />
                            <span v-if="invoiceForm.errors.check_number" class="mt-1 block text-xs font-semibold text-red-600">{{ invoiceForm.errors.check_number }}</span>
                        </label>

                        <label class="block">
                            <span class="field-label">Bill-to address</span>
                            <textarea v-model.trim="invoiceForm.billing_address" class="field-input min-h-24" maxlength="600" placeholder="Street address&#10;City, State ZIP" />
                            <span v-if="invoiceForm.errors.billing_address" class="mt-1 block text-xs font-semibold text-red-600">{{ invoiceForm.errors.billing_address }}</span>
                        </label>

                        <label class="block">
                            <span class="field-label">Ship-to address</span>
                            <textarea v-model.trim="invoiceForm.shipping_address" class="field-input min-h-24" maxlength="600" placeholder="Leave blank to use the bill-to address" />
                            <span v-if="invoiceForm.errors.shipping_address" class="mt-1 block text-xs font-semibold text-red-600">{{ invoiceForm.errors.shipping_address }}</span>
                        </label>

                        <button class="btn-primary w-full" type="submit" :disabled="invoiceForm.processing"><Save class="size-4" /> {{ invoiceForm.processing ? 'Saving…' : 'Save invoice details' }}</button>
                    </form>
                </section>

                <OrderTotals :subtotal="order.subtotal" :total="order.total" />
            </aside>
        </div>
        <DeleteOrderDialog :open="confirmingDelete" :order="order" @close="confirmingDelete = false" @finished="confirmingDelete = false" />
    </AdminLayout>
</template>
