<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Eye } from '@lucide/vue';
import DataTable from '@/Components/Admin/DataTable.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import Pagination from '@/Components/Shared/Pagination.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { Paginated } from '@/types/catalogue';
import type { OrderDeletionNotice } from '@/types/orders';
import { formatDateTime, formatMoney } from '@/Utils/format';

defineProps<{ notices: Paginated<OrderDeletionNotice> }>();
</script>

<template>
    <Head title="Order notices" />
    <AdminLayout>
        <PageHeader eyebrow="Audit trail" title="Order notices" :description="`${notices.total} permanent record${notices.total === 1 ? '' : 's'} of deleted orders and inventory decisions.`" />
        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm leading-6 text-blue-950">Deleting an order never erases its history here. Each notice records the customer, line items, quantities, totals, administrator, and whether reserved stock was returned.</div>
        <div class="mt-5">
            <DataTable label="Deleted order notices">
                <thead><tr><th class="px-4 py-3">Order</th><th class="px-4 py-3">Shop / owner</th><th class="px-4 py-3">Inventory decision</th><th class="px-4 py-3">Total</th><th class="hidden px-4 py-3 lg:table-cell">Recorded</th><th class="px-4 py-3 text-right">View</th></tr></thead>
                <tbody class="divide-y divide-tabarak-line">
                    <tr v-for="notice in notices.data" :key="notice.id">
                        <td class="px-4 py-3"><Link :href="route('admin.order-notices.show', notice.id)" class="font-bold text-tabarak-blue hover:text-tabarak-orange">{{ notice.order_number }}</Link><p class="mt-1 text-xs text-slate-400">{{ notice.items_count }} item{{ notice.items_count === 1 ? '' : 's' }}</p></td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-tabarak-ink">{{ notice.customer_name }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ notice.customer_phone }}</p>
                            <p v-if="notice.customer_address" class="mt-0.5 text-xs text-slate-400 truncate max-w-xs" :title="notice.customer_address">{{ notice.customer_address }}</p>
                        </td>
                        <td class="px-4 py-3"><p class="font-semibold" :class="notice.deletion_mode === 'cancel_restore_stock' ? 'text-emerald-700' : 'text-tabarak-orange'">{{ notice.deletion_mode === 'cancel_restore_stock' ? 'Cancelled; stock returned' : 'Record deleted; stock kept' }}</p><p class="mt-1 text-xs text-slate-500">{{ notice.restored_quantity }} unit{{ notice.restored_quantity === 1 ? '' : 's' }} restored</p></td>
                        <td class="px-4 py-3 font-bold text-tabarak-ink">{{ formatMoney(notice.total) }}</td>
                        <td class="hidden px-4 py-3 text-slate-500 lg:table-cell">{{ formatDateTime(notice.recorded_at) }}</td>
                        <td class="px-4 py-3 text-right"><Link :href="route('admin.order-notices.show', notice.id)" class="admin-table-action ml-auto" aria-label="View deletion notice"><Eye class="size-4" /></Link></td>
                    </tr>
                    <tr v-if="!notices.data.length"><td colspan="6" class="px-4 py-12 text-center text-sm text-slate-500">No orders have been deleted.</td></tr>
                </tbody>
            </DataTable>
        </div>
        <div class="mt-7"><Pagination :links="notices.links" /></div>
    </AdminLayout>
</template>
