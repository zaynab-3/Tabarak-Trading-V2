<script setup lang="ts">
import { CheckCircle2, CircleAlert } from '@lucide/vue';

defineProps<{ success?: string; error?: string }>();
</script>

<template>
    <div v-if="success || error" class="mb-5 flex items-start gap-3 rounded-md border px-4 py-3 text-sm" :class="success ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'border-red-200 bg-red-50 text-red-900'" role="status">
        <CheckCircle2 v-if="success" class="mt-0.5 size-5 shrink-0" aria-hidden="true" />
        <CircleAlert v-else class="mt-0.5 size-5 shrink-0" aria-hidden="true" />
        <p>{{ success || error }}</p>
    </div>
</template>
