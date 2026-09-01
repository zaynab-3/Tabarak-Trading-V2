<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, BotOff } from '@lucide/vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import StatusBadge from '@/Components/Admin/StatusBadge.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { ImportBatch } from '@/types/catalogue';

defineProps<{ batch: ImportBatch }>();
</script>

<template>
    <Head :title="batch.name || `Import #${batch.id}`" /><AdminLayout><PageHeader eyebrow="Import review" :title="batch.name || `Import #${batch.id}`" :description="`${batch.processed_items} of ${batch.total_items} items prepared for review.`"><Link :href="route('admin.imports.index')" class="btn-secondary"><ArrowLeft class="size-4" /> All batches</Link></PageHeader>
        <div class="mb-6 flex items-start gap-3 border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900"><BotOff class="mt-0.5 size-5 shrink-0" /><p>The placeholder analyzer is active. It creates review-ready items with a warning, but makes no product guesses and never publishes.</p></div>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"><article v-for="item in batch.items" :key="item.id" class="surface overflow-hidden"><div class="aspect-[4/3] bg-oat-100"><img :src="item.media.url" :alt="item.media.alt_text || `Import image ${item.id}`" class="h-full w-full object-cover" /></div><div class="p-4"><div class="flex items-center justify-between"><span class="text-xs font-bold text-slate-400">Item #{{ item.id }}</span><StatusBadge :status="item.status" /></div><h2 class="mt-3 font-display text-xl font-bold text-forest-900">{{ item.suggested_name || 'Awaiting manual identification' }}</h2><p class="mt-2 text-sm text-slate-500">{{ [item.suggested_brand, item.suggested_category, item.suggested_weight].filter(Boolean).join(' · ') || 'No automated suggestions configured.' }}</p><p v-if="item.warnings?.length" class="mt-3 border-l-2 border-saffron-500 pl-3 text-xs leading-5 text-slate-600">{{ item.warnings.join(' ') }}</p></div></article></div>
    </AdminLayout>
</template>
