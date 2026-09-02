<script setup lang="ts">
import type { Component } from 'vue';

defineProps<{ label: string; value: number | string; icon: Component; tone?: 'blue' | 'orange' }>();
</script>

<template>
    <div class="surface flex min-h-24 items-center gap-4 p-4 md:p-5">
        <span class="grid size-10 shrink-0 place-items-center rounded-lg" :class="tone === 'orange' ? 'bg-[#FFF0E8] text-tabarak-orange' : 'bg-tabarak-mist text-tabarak-blue'"><component :is="icon" class="size-5" /></span>
        <div class="min-w-0"><p class="truncate text-sm font-medium text-slate-500">{{ label }}</p><p class="mt-1 text-2xl font-semibold tracking-tight text-tabarak-ink">{{ value }}</p></div>
    </div>
</template>
