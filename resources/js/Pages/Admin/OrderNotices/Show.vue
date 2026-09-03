<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, MapPin, RotateCcw, ShieldCheck } from '@lucide/vue';
import OrderDeletionItems from '@/Components/Admin/OrderDeletionItems.vue';
import OrderTotals from '@/Components/Orders/OrderTotals.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { OrderDeletionNotice } from '@/types/orders';
import { formatDateTime } from '@/Utils/format';

defineProps<{ notice: OrderDeletionNotice }>();
</script>

<template>
    <Head :title="`Deleted ${notice.order_number}`" />
    <AdminLayout>
        <PageHeader eyebrow="Deletion notice" :title="notice.order_number" :description="`Recorded ${formatDateTime(notice.recorded_at)}`">
            <Link :href="route('admin.order-notices.index')" class="btn-secondary"><ArrowLeft class="size-4" /> All notices</Link>
        </PageHeader>

        <div class="mb-6 flex items-start gap-3 rounded-lg border p-4" :class="notice.deletion_mode === 'cancel_restore_stock' ? 'border-emerald-200 bg-emerald-50 text-emerald-950' : 'border-amber-200 bg-amber-50 text-amber-950'">
            <RotateCcw v-if="notice.deletion_mode === 'cancel_restore_stock'" class="mt-0.5 size-5 shrink-0" />
            <ShieldCheck v-else class="mt-0.5 size-5 shrink-0" />
            <div><p class="font-bold">{{ notice.deletion_mode === 'cancel_restore_stock' ? 'Order cancelled and tracked stock returned' : 'Order record deleted without returning stock' }}</p><p class="mt-1 text-sm leading-6">{{ notice.restored_quantity }} tracked unit{{ notice.restored_quantity === 1 ? '' : 's' }} returned. Open-quantity products were not changed.</p></div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px] xl:items-start">
            <section><h2 class="admin-section-title mb-3">Deleted order items</h2><OrderDeletionItems :items="notice.items || []" /></section>
            <aside class="space-y-4 xl:sticky xl:top-24">
                <section class="surface p-5">
                    <p class="eyebrow">Customer</p>
                    <h2 class="mt-1 text-xl font-bold text-tabarak-ink">{{ notice.customer_name }}</h2>
                    <p class="mt-2 text-sm font-bold text-tabarak-blue">{{ notice.customer_phone }}</p>
                    <p v-if="notice.customer_address" class="mt-2 flex items-start gap-1.5 text-xs text-slate-600">
                        <MapPin class="size-3.5 shrink-0 mt-0.5 text-slate-400" />
                        <span>{{ notice.customer_address }}</span>
                    </p>
                    <dl class="mt-5 space-y-3 border-t border-tabarak-line pt-4 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Original status</dt><dd class="font-semibold capitalize">{{ notice.order_status }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Submitted</dt><dd class="text-right font-semibold">{{ formatDateTime(notice.submitted_at) }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Deleted by</dt><dd class="text-right font-semibold">{{ notice.deleted_by || 'Former administrator' }}</dd></div>
                    </dl>
                </section>
                <OrderTotals :subtotal="notice.subtotal" :total="notice.total" />
            </aside>
        </div>
    </AdminLayout>
</template>
