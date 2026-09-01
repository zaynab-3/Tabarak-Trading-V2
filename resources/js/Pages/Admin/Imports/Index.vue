<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, ShieldCheck } from '@lucide/vue';
import AiAnalyzerStatus from '@/Components/Admin/AiAnalyzerStatus.vue';
import BulkImportUploader from '@/Components/Admin/BulkImportUploader.vue';
import DataTable from '@/Components/Admin/DataTable.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import StatusBadge from '@/Components/Admin/StatusBadge.vue';
import Pagination from '@/Components/Shared/Pagination.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { ImportBatch, ImportConfiguration, Paginated } from '@/types/catalogue';
import { formatDate } from '@/Utils/format';

defineProps<{ batches: Paginated<ImportBatch>; importConfig: ImportConfiguration }>();
</script>

<template>
    <Head title="Bulk import" />
    <AdminLayout>
        <PageHeader
            eyebrow="AI-assisted import"
            title="Bulk product image import"
            description="Upload a large image set once, then review each AI-assisted product draft before publishing."
        />

        <AiAnalyzerStatus :analyzer="importConfig.analyzer" class="mb-5" />

        <div class="mb-7 grid gap-5 lg:grid-cols-[minmax(0,1fr)_300px]">
            <BulkImportUploader :config="importConfig" />
            <aside class="rounded-lg bg-tabarak-blue p-6 text-white shadow-[0_14px_34px_rgba(64,88,225,0.18)]">
                <span class="grid size-12 place-items-center rounded-lg bg-white/10 text-[#FFC2A5]"><ShieldCheck class="size-6" /></span>
                <h2 class="mt-5 font-display text-2xl font-bold">Human approval stays mandatory.</h2>
                <p class="mt-3 text-sm leading-6 text-white/75">Detected names and package details remain drafts. AI never publishes products automatically.</p>
            </aside>
        </div>

        <div class="mb-3 flex items-center justify-between">
            <h2 class="admin-section-title">Import history</h2>
            <span class="text-xs font-semibold text-slate-400">{{ batches.total }} batches</span>
        </div>
        <DataTable label="Import batches">
            <thead class="text-xs uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">Batch</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Progress</th>
                    <th class="hidden px-4 py-3 lg:table-cell">Created by</th>
                    <th class="hidden px-4 py-3 xl:table-cell">Created</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-tabarak-line">
                <tr v-for="batch in batches.data" :key="batch.id">
                    <td class="px-4 py-3 font-bold text-tabarak-ink">{{ batch.name }}</td>
                    <td class="px-4 py-3"><StatusBadge :status="batch.status" /></td>
                    <td class="px-4 py-3 text-slate-500">{{ batch.processed_items }} / {{ batch.total_items }}</td>
                    <td class="hidden px-4 py-3 text-slate-500 lg:table-cell">{{ batch.creator?.name || 'Former admin' }}</td>
                    <td class="hidden px-4 py-3 text-slate-500 xl:table-cell">{{ formatDate(batch.created_at) }}</td>
                    <td class="px-4 py-3 text-right">
                        <Link :href="route('admin.imports.show', batch.id)" class="inline-flex min-h-10 items-center gap-1 rounded-md px-2 text-sm font-bold text-tabarak-blue hover:text-tabarak-orange">
                            Review <ArrowRight class="size-4" />
                        </Link>
                    </td>
                </tr>
            </tbody>
        </DataTable>
        <div class="mt-8"><Pagination :links="batches.links" /></div>
    </AdminLayout>
</template>
