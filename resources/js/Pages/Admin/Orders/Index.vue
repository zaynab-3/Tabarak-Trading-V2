<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Check, Eye, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import DeleteOrderDialog from '@/Components/Admin/DeleteOrderDialog.vue';
import DataTable from '@/Components/Admin/DataTable.vue';
import OrderFilters from '@/Components/Admin/OrderFilters.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import StatusBadge from '@/Components/Admin/StatusBadge.vue';
import Pagination from '@/Components/Shared/Pagination.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { Paginated } from '@/types/catalogue';
import type { Order, OrderFilters as Filters } from '@/types/orders';
import { formatDateTime, formatMoney } from '@/Utils/format';

defineProps<{ orders: Paginated<Order>; filters: Filters; statuses: string[] }>();
const pendingDelete = ref<Order | null>(null);
const complete = (order: Order) => router.patch(route('admin.orders.complete', order.public_token), {}, { preserveScroll: true });
</script>

<template>
    <Head title="Orders" />
    <AdminLayout>
        <PageHeader eyebrow="Sales" title="Orders" :description="`${orders.total} customer orders and invoice records.`" />
        <OrderFilters :filters="filters" :statuses="statuses" />
        <div class="mt-5">
            <DataTable label="Orders">
                <thead><tr><th class="px-4 py-3">Order</th><th class="px-4 py-3">Shop / owner</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Total</th><th class="hidden px-4 py-3 lg:table-cell">Submitted</th><th class="px-4 py-3 text-right">Actions</th></tr></thead>
                <tbody class="divide-y divide-tabarak-line">
                    <tr v-for="order in orders.data" :key="order.id">
                        <td class="px-4 py-3"><Link :href="route('admin.orders.show', order.public_token)" class="font-bold text-tabarak-blue hover:text-tabarak-orange">{{ order.order_number }}</Link><p class="mt-1 text-xs text-slate-400">{{ order.items_count }} line item{{ order.items_count === 1 ? '' : 's' }}</p></td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-tabarak-ink">{{ order.customer_name }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ order.customer_phone }}</p>
                            <p v-if="order.customer_address" class="mt-0.5 text-xs text-slate-400 truncate max-w-xs" :title="order.customer_address">{{ order.customer_address }}</p>
                        </td>
                        <td class="px-4 py-3"><StatusBadge :status="order.status" /></td>
                        <td class="px-4 py-3 font-bold text-tabarak-ink">{{ formatMoney(order.total) }}</td>
                        <td class="hidden px-4 py-3 text-slate-500 lg:table-cell">{{ formatDateTime(order.submitted_at) }}</td>
                        <td class="px-4 py-3"><div class="flex justify-end gap-1.5"><Link :href="route('admin.orders.show', order.public_token)" class="admin-table-action" aria-label="View order"><Eye class="size-4" /></Link><button v-if="order.status !== 'completed'" class="admin-table-action text-emerald-600 hover:border-emerald-500 hover:text-emerald-700" type="button" aria-label="Mark order completed" @click="complete(order)"><Check class="size-4" /></button><button class="admin-table-action border-red-200 text-red-600 hover:border-red-300 hover:text-red-700" type="button" aria-label="Delete order" @click="pendingDelete = order"><Trash2 class="size-4" /></button></div></td>
                    </tr>
                    <tr v-if="!orders.data.length"><td colspan="6" class="px-4 py-12 text-center text-sm text-slate-500">No orders match these filters.</td></tr>
                </tbody>
            </DataTable>
        </div>
        <div class="mt-7"><Pagination :links="orders.links" /></div>
        <DeleteOrderDialog :open="Boolean(pendingDelete)" :order="pendingDelete" @close="pendingDelete = null" @finished="pendingDelete = null" />
    </AdminLayout>
</template>
