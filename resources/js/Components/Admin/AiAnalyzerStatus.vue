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
                {{ analyzer.enabled ? `${analyzer.provider} image analysis is active` : `${analyzer.provider} needs an API key` }}
            </p>
            <p class="mt-1 leading-6">
                <template v-if="analyzer.enabled">
                    {{ analyzer.model }} analyzes every uploaded image in the queue. Suggestions remain drafts until an admin approves them.
                </template>
                <template v-else>
                    Uploads still create review items, but automatic names stay disabled until the key is added to the local <code>.env</code> file.
                </template>
            </p>
        </div>
    </div>
</template>
