<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, LoaderCircle } from '@lucide/vue';
import AiAnalyzerStatus from '@/Components/Admin/AiAnalyzerStatus.vue';
import ImportAnalysisCard from '@/Components/Admin/ImportAnalysisCard.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import ReanalyzeImportBatchButton from '@/Components/Admin/ReanalyzeImportBatchButton.vue';
import { useImportBatchPolling } from '@/Composables/useImportBatchPolling';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { ImportBatch, ProductImageAnalyzerStatus } from '@/types/catalogue';

const props = defineProps<{
    batch: ImportBatch;
    analyzer: ProductImageAnalyzerStatus;
    canReanalyze: boolean;
}>();

useImportBatchPolling(
    () => props.batch.total_items > 0 && props.batch.processed_items < props.batch.total_items,
);
</script>

<template>
    <Head :title="batch.name || `Import #${batch.id}`" />
    <AdminLayout>
        <PageHeader
            eyebrow="Import review"
            :title="batch.name || `Import #${batch.id}`"
            :description="`${batch.processed_items} of ${batch.total_items} images analyzed or prepared for review.`"
        >
            <div class="flex flex-wrap items-center gap-2">
                <ReanalyzeImportBatchButton v-if="canReanalyze" :batch-id="batch.id" />
                <Link :href="route('admin.imports.index')" class="btn-secondary">
                    <ArrowLeft class="size-4" /> All batches
                </Link>
            </div>
        </PageHeader>

        <AiAnalyzerStatus :analyzer="analyzer" class="mb-5" />
        <div
            v-if="batch.processed_items < batch.total_items"
            class="mb-5 flex items-center gap-3 border border-blue-200 bg-blue-50 p-4 text-sm font-semibold text-blue-900"
        >
            <LoaderCircle class="size-5 animate-spin" />
            Analysis is running. This page refreshes automatically as each product finishes.
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <ImportAnalysisCard v-for="item in batch.items" :key="item.id" :item="item" />
        </div>
    </AdminLayout>
</template>
