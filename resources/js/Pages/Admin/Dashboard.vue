<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Boxes, Building2, ClipboardList, FolderTree, Images, Plus, SendToBack, Sparkles, UploadCloud } from '@lucide/vue';
import DataTable from '@/Components/Admin/DataTable.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import QuickActionCard from '@/Components/Admin/QuickActionCard.vue';
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
    };
    recentProducts: Product[];
    recentImports: ImportBatch[];
    recentOrders: Order[];
}>();
</script>

<template>
    <Head title="Admin dashboard" />
    <AdminLayout>
        <PageHeader eyebrow="Overview" title="Catalogue dashboard" description="Your catalogue, import queue, and content work in one focused workspace.">
            <Link :href="route('admin.products.create')" class="btn-primary"><Plus class="size-4" /> Add product</Link>
        </PageHeader>

        <section aria-labelledby="catalogue-health">
            <div class="mb-3 flex items-center justify-between">
                <h2 id="catalogue-health" class="text-sm font-bold text-tabarak-ink">Catalogue health</h2>
                <span class="text-xs font-semibold text-slate-400">Live overview</span>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-3 2xl:grid-cols-7">
                <StatCard label="Pending orders" :value="stats.pendingOrders" :icon="ClipboardList" tone="orange" />
                <StatCard label="Products" :value="stats.products" :icon="Boxes" />
                <StatCard label="Published" :value="stats.published" :icon="SendToBack" tone="orange" />
                <StatCard label="Categories" :value="stats.categories" :icon="FolderTree" />
                <StatCard label="Brands" :value="stats.brands" :icon="Building2" />
                <StatCard label="Media" :value="stats.media" :icon="Images" />
                <StatCard label="Import review" :value="stats.importsAwaitingReview" :icon="Sparkles" tone="orange" />
            </div>
        </section>

        <section class="mt-7" aria-labelledby="quick-actions">
            <h2 id="quick-actions" class="mb-3 text-sm font-bold text-tabarak-ink">Quick actions</h2>
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <QuickActionCard title="Review orders" description="Open customer orders, invoices, and completion status." :href="route('admin.orders.index')" :icon="ClipboardList" tone="orange" />
                <QuickActionCard title="Add a product" description="Create a catalogue listing and add its pack details." :href="route('admin.products.create')" :icon="Plus" />
                <QuickActionCard title="Upload media" description="Add, edit, delete, or reorder catalogue images." :href="route('admin.media.index')" :icon="UploadCloud" />
                <QuickActionCard title="Start bulk import" description="Upload product images and prepare AI-assisted drafts." :href="route('admin.imports.index')" :icon="Sparkles" tone="orange" />
            </div>
        </section>

        <section class="mt-7">
            <div class="mb-3 flex items-center justify-between"><h2 class="admin-section-title">Recent customer orders</h2><Link :href="route('admin.orders.index')" class="text-sm font-bold text-tabarak-blue hover:text-tabarak-orange">View all</Link></div>
            <DataTable label="Recent orders">
                <thead><tr><th class="px-4 py-3">Order</th><th class="px-4 py-3">Shop / owner</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Total</th><th class="hidden px-4 py-3 md:table-cell">Submitted</th></tr></thead>
                <tbody class="divide-y divide-tabarak-line">
                    <tr v-for="order in recentOrders" :key="order.id"><td class="px-4 py-3"><Link :href="route('admin.orders.show', order.public_token)" class="font-bold text-tabarak-blue hover:text-tabarak-orange">{{ order.order_number }}</Link></td><td class="px-4 py-3"><p class="font-semibold text-tabarak-ink">{{ order.customer_name }}</p><p class="mt-1 text-xs text-slate-500">{{ order.customer_phone }}</p></td><td class="px-4 py-3"><StatusBadge :status="order.status" /></td><td class="px-4 py-3 font-bold text-tabarak-ink">{{ formatMoney(order.total) }}</td><td class="hidden px-4 py-3 text-slate-500 md:table-cell">{{ formatDateTime(order.submitted_at) }}</td></tr>
                    <tr v-if="!recentOrders.length"><td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">New storefront orders will appear here.</td></tr>
                </tbody>
            </DataTable>
        </section>

        <div class="mt-7 grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(300px,0.65fr)]">
            <section class="min-w-0">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-tabarak-ink">Recently updated products</h2>
                    <Link :href="route('admin.products.index')" class="text-sm font-bold text-tabarak-blue hover:text-tabarak-orange">View all</Link>
                </div>
                <DataTable label="Recent products">
                    <thead class="bg-tabarak-mist text-xs uppercase tracking-wider text-slate-500">
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
                    <h2 class="text-sm font-bold text-tabarak-ink">Recent imports</h2>
                    <Link :href="route('admin.imports.index')" class="text-sm font-bold text-tabarak-blue hover:text-tabarak-orange">View all</Link>
                </div>
                <div class="surface divide-y divide-tabarak-line">
                    <Link v-for="batch in recentImports" :key="batch.id" :href="route('admin.imports.show', batch.id)" class="flex min-h-20 items-center justify-between gap-3 p-4 transition hover:bg-tabarak-mist">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-tabarak-ink">{{ batch.name }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ batch.total_items }} images · {{ formatDate(batch.created_at) }}</p>
                        </div>
                        <StatusBadge :status="batch.status" />
                    </Link>
                    <p v-if="!recentImports.length" class="p-8 text-center text-sm text-slate-500">No imports yet.</p>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>
