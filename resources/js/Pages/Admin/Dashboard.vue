<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, BellRing, Boxes, Building2, ClipboardList, FolderTree, Images, Plus, SendToBack, Sparkles } from '@lucide/vue';
import DataTable from '@/Components/Admin/DataTable.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import StatCard from '@/Components/Admin/StatCard.vue';
import StatusBadge from '@/Components/Admin/StatusBadge.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { ImportBatch, Product } from '@/types/catalogue';
import type { Order } from '@/types/orders';
import { formatDate, formatDateTime, formatMoney } from '@/Utils/format';

defineProps<{
    stats: {
        products: number;
        published: number;
        categories: number;
        brands: number;
        media: number;
        importsAwaitingReview: number;
        pendingOrders: number;
        orderNotices: number;
    };
    recentProducts: Product[];
    recentImports: ImportBatch[];
    recentOrders: Order[];
}>();
</script>

<template>
    <Head title="Admin dashboard" />
    <AdminLayout>
        <PageHeader title="Good afternoon" description="Here is what needs your attention today.">
            <Link :href="route('admin.products.create')" class="btn-primary"><Plus class="size-4" /> Add product</Link>
        </PageHeader>

        <section aria-labelledby="attention-needed">
            <div class="mb-3 flex items-center justify-between">
                <h2 id="attention-needed" class="text-base font-semibold text-tabarak-ink">Attention needed</h2>
            </div>
            <div class="grid gap-3 lg:grid-cols-3">
                <Link :href="route('admin.orders.index')" class="group surface flex min-h-28 items-center gap-4 p-5 transition hover:border-tabarak-line hover:shadow-[0_8px_24px_rgba(21,24,42,0.06)]">
                    <span class="grid size-11 shrink-0 place-items-center rounded-lg bg-[#FFF0E8] text-tabarak-orange"><ClipboardList class="size-5" /></span>
                    <span class="min-w-0 flex-1"><span class="block text-2xl font-semibold text-tabarak-ink">{{ stats.pendingOrders }}</span><span class="mt-1 block text-sm text-slate-500">Pending customer orders</span></span>
                    <ArrowRight class="size-4 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-tabarak-blue" />
                </Link>
                <Link :href="route('admin.imports.index')" class="group surface flex min-h-28 items-center gap-4 p-5 transition hover:border-tabarak-line hover:shadow-[0_8px_24px_rgba(21,24,42,0.06)]">
                    <span class="grid size-11 shrink-0 place-items-center rounded-lg bg-tabarak-mist text-tabarak-blue"><Sparkles class="size-5" /></span>
                    <span class="min-w-0 flex-1"><span class="block text-2xl font-semibold text-tabarak-ink">{{ stats.importsAwaitingReview }}</span><span class="mt-1 block text-sm text-slate-500">Imports waiting for review</span></span>
                    <ArrowRight class="size-4 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-tabarak-blue" />
                </Link>
                <Link :href="route('admin.order-notices.index')" class="group surface flex min-h-28 items-center gap-4 p-5 transition hover:border-tabarak-line hover:shadow-[0_8px_24px_rgba(21,24,42,0.06)]">
                    <span class="grid size-11 shrink-0 place-items-center rounded-lg bg-tabarak-mist text-tabarak-blue"><BellRing class="size-5" /></span>
                    <span class="min-w-0 flex-1"><span class="block text-2xl font-semibold text-tabarak-ink">{{ stats.orderNotices }}</span><span class="mt-1 block text-sm text-slate-500">Unread order notices</span></span>
                    <ArrowRight class="size-4 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-tabarak-blue" />
                </Link>
            </div>
        </section>

        <section class="mt-8" aria-labelledby="catalogue-overview">
            <h2 id="catalogue-overview" class="mb-3 text-base font-semibold text-tabarak-ink">Catalogue overview</h2>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                <StatCard label="Products" :value="stats.products" :icon="Boxes" />
                <StatCard label="Published" :value="stats.published" :icon="SendToBack" tone="orange" />
                <StatCard label="Categories" :value="stats.categories" :icon="FolderTree" />
                <StatCard label="Brands" :value="stats.brands" :icon="Building2" />
                <StatCard label="Media" :value="stats.media" :icon="Images" />
            </div>
        </section>

        <div class="mt-8 grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(300px,0.65fr)]">
            <section class="min-w-0">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-tabarak-ink">Recently updated products</h2>
                    <Link :href="route('admin.products.index')" class="text-sm font-bold text-tabarak-blue hover:text-tabarak-orange">View all</Link>
                </div>
                <DataTable label="Recent products">
                    <thead class="bg-tabarak-mist text-xs text-slate-500">
                        <tr><th class="px-4 py-3">Product</th><th class="px-4 py-3">Category</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Updated</th></tr>
                    </thead>
                    <tbody class="divide-y divide-tabarak-line">
                        <tr v-for="product in recentProducts" :key="product.id" class="transition hover:bg-tabarak-mist/70">
                            <td class="px-4 py-3 font-semibold text-tabarak-ink">{{ product.name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ product.category?.name || 'Uncategorized' }}</td>
                            <td class="px-4 py-3"><StatusBadge :status="product.status" /></td>
                            <td class="px-4 py-3 text-slate-500">{{ formatDate(product.updated_at) }}</td>
                        </tr>
                    </tbody>
                </DataTable>
            </section>

            <section>
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-tabarak-ink">Recent imports</h2>
                    <Link :href="route('admin.imports.index')" class="text-sm font-bold text-tabarak-blue hover:text-tabarak-orange">View all</Link>
                </div>
                <div class="surface divide-y divide-tabarak-line">
                    <Link v-for="batch in recentImports" :key="batch.id" :href="route('admin.imports.show', batch.id)" class="flex min-h-20 items-center justify-between gap-3 p-4 transition hover:bg-tabarak-mist">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-tabarak-ink">{{ batch.name }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ batch.total_items }} images / {{ formatDate(batch.created_at) }}</p>
                        </div>
                        <StatusBadge :status="batch.status" />
                    </Link>
                    <p v-if="!recentImports.length" class="p-8 text-center text-sm text-slate-500">No imports yet.</p>
                </div>
            </section>
        </div>

        <section class="mt-8">
            <div class="mb-3 flex items-center justify-between"><h2 class="text-base font-semibold text-tabarak-ink">Recent customer orders</h2><Link :href="route('admin.orders.index')" class="text-sm font-semibold text-tabarak-blue hover:text-tabarak-orange">View all</Link></div>
            <DataTable label="Recent orders">
                <thead><tr><th class="px-4 py-3">Order</th><th class="px-4 py-3">Shop / owner</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Total</th><th class="hidden px-4 py-3 md:table-cell">Submitted</th></tr></thead>
                <tbody class="divide-y divide-tabarak-line">
                    <tr v-for="order in recentOrders" :key="order.id">
                        <td class="px-4 py-3"><Link :href="route('admin.orders.show', order.public_token)" class="font-semibold text-tabarak-blue hover:text-tabarak-orange">{{ order.order_number }}</Link></td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-tabarak-ink">{{ order.customer_name }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ order.customer_phone }}</p>
                            <p v-if="order.customer_address" class="mt-0.5 text-xs text-slate-400 truncate max-w-xs" :title="order.customer_address">{{ order.customer_address }}</p>
                        </td>
                        <td class="px-4 py-3"><StatusBadge :status="order.status" /></td>
                        <td class="px-4 py-3 font-semibold text-tabarak-ink">{{ formatMoney(order.total) }}</td>
                        <td class="hidden px-4 py-3 text-slate-500 md:table-cell">{{ formatDateTime(order.submitted_at) }}</td>
                    </tr>
                    <tr v-if="!recentOrders.length"><td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">New storefront orders will appear here.</td></tr>
                </tbody>
            </DataTable>
        </section>
    </AdminLayout>
</template>
