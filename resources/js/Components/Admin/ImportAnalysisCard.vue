<script setup lang="ts">
import StatusBadge from '@/Components/Admin/StatusBadge.vue';
import type { ImportItem } from '@/types/catalogue';

defineProps<{ item: ImportItem }>();

const confidenceLabel = (confidence: string | null) => {
    if (!confidence) {
        return null;
    }

    return `${Math.round(Number(confidence) * 100)}% confidence`;
};
</script>

<template>
    <article class="surface overflow-hidden">
        <div class="aspect-[4/3] bg-oat-100">
            <img
                :src="item.media.url"
                :alt="item.media.alt_text || `Import image ${item.id}`"
                class="h-full w-full object-contain"
            />
        </div>
        <div class="p-4">
            <div class="flex items-center justify-between gap-3">
                <span class="text-xs font-bold text-slate-400">Item #{{ item.id }}</span>
                <StatusBadge :status="item.status" />
            </div>
            <h2 class="mt-3 font-display text-xl font-bold text-forest-900">
                {{ item.suggested_name || (['pending', 'processing'].includes(item.status) ? 'Analyzing product…' : 'Needs manual identification') }}
            </h2>
            <p class="mt-2 text-sm text-slate-500">
                {{ [item.suggested_brand, item.suggested_category, item.suggested_weight].filter(Boolean).join(' · ') || 'No confident product details yet.' }}
            </p>
            <p v-if="confidenceLabel(item.confidence)" class="mt-2 text-xs font-bold text-emerald-700">
                {{ confidenceLabel(item.confidence) }}
            </p>
            <dl v-if="item.suggested_metadata" class="mt-4 grid grid-cols-2 gap-2 text-xs">
                <template v-for="(value, key) in item.suggested_metadata" :key="key">
                    <div v-if="value" class="rounded bg-oat-50 p-2">
                        <dt class="font-bold capitalize text-slate-500">{{ String(key).replaceAll('_', ' ') }}</dt>
                        <dd class="mt-1 line-clamp-3 text-forest-900">{{ value }}</dd>
                    </div>
                </template>
            </dl>
            <p v-if="item.warnings?.length" class="mt-3 border-l-2 border-saffron-500 pl-3 text-xs leading-5 text-slate-600">
                {{ item.warnings.join(' ') }}
            </p>
        </div>
    </article>
</template>
