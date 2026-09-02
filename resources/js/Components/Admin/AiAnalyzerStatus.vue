<script setup lang="ts">
import { BotOff, Sparkles } from '@lucide/vue';
import type { ProductImageAnalyzerStatus } from '@/types/catalogue';

defineProps<{ analyzer: ProductImageAnalyzerStatus }>();
</script>

<template>
    <div
        class="flex items-start gap-3 rounded-lg border p-4 text-sm"
        :class="analyzer.enabled ? 'border-emerald-200 bg-emerald-50 text-emerald-950' : 'border-amber-200 bg-amber-50 text-amber-950'"
    >
        <Sparkles v-if="analyzer.enabled" class="mt-0.5 size-5 shrink-0" />
        <BotOff v-else class="mt-0.5 size-5 shrink-0" />
        <div>
            <p class="font-bold">
                {{ analyzer.enabled ? `${analyzer.provider} is active` : 'Automatic image analysis is not configured' }}
            </p>
            <p class="mt-1 leading-6">
                <template v-if="analyzer.enabled">
                    {{ analyzer.model }} processes uploaded images in the queue. If it cannot identify a product, the item remains available for manual admin review.
                </template>
                <template v-else>
                    Uploads still create review items and remain editable by an administrator even when AI and OCR are unavailable.
                </template>
            </p>
        </div>
    </div>
</template>
