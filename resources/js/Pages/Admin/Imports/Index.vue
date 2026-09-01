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

defineProps<{
    batches: Paginated<ImportBatch>;
    importConfig: ImportConfiguration;
}>();
</script>

<template>
    <Head title="Bulk import" />
    <AdminLayout>
        <PageHeader
            eyebrow="AI-assisted import"
            title="Bulk product image import"
            description="Select a large set once. Images upload safely in smaller groups and each product is analyzed in the background."
        />

        <AiAnalyzerStatus :analyzer="importConfig.analyzer" class="mb-5" />

        <div class="mb-6 grid gap-5 lg:grid-cols-[1fr_320px]">
            <BulkImportUploader :config="importConfig" />
            <aside class="bg-forest-900 p-6 text-white">
                <ShieldCheck class="size-8 text-saffron-400" />
                <h2 class="mt-5 font-display text-2xl font-bold">Human approval stays mandatory.</h2>
                <p class="mt-3 text-sm leading-6 text-forest-100">
                    Detected names and package details are saved as drafts. AI never publishes products automatically.
                </p>
            </aside>
        </div>

        <DataTable label="Import batches">
            <thead class="bg-oat-100 text-xs uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">Batch</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Progress</th>
                    <th class="px-4 py-3">Created by</th>
                    <th class="px-4 py-3">Created</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-oat-200">
                <tr v-for="batch in batches.data" :key="batch.id">
                    <td class="px-4 py-3 font-bold text-forest-900">{{ batch.name }}</td>
                    <td class="px-4 py-3"><StatusBadge :status="batch.status" /></td>
                    <td class="px-4 py-3 text-slate-500">{{ batch.processed_items }} / {{ batch.total_items }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ batch.creator?.name || 'Former admin' }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ formatDate(batch.created_at) }}</td>
                    <td class="px-4 py-3 text-right">
                        <Link :href="route('admin.imports.show', batch.id)" class="inline-flex items-center gap-1 text-sm font-bold text-forest-700">
                            Review <ArrowRight class="size-4" />
                        </Link>
                    </td>
                </tr>
            </tbody>
        </DataTable>
        <div class="mt-8"><Pagination :links="batches.links" /></div>
    </AdminLayout>
</template>
