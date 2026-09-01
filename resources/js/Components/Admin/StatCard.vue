<script setup lang="ts">
import type { Component } from 'vue';

defineProps<{ label: string; value: number | string; icon: Component; tone?: 'green' | 'gold' }>();
</script>

<template>
    <div class="surface flex items-center gap-4 p-5">
        <span class="grid size-12 shrink-0 place-items-center rounded-md" :class="tone === 'gold' ? 'bg-saffron-100 text-saffron-600' : 'bg-forest-50 text-forest-700'"><component :is="icon" class="size-5" /></span>
        <div><p class="text-sm font-semibold text-slate-500">{{ label }}</p><p class="mt-1 text-2xl font-bold text-forest-900">{{ value }}</p></div>
    </div>
</template>
