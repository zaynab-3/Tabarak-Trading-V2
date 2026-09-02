<script setup lang="ts">
defineProps<{ eyebrow?: string; title: string; description?: string }>();
</script>

<template>
    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="min-w-0">
            <p v-if="eyebrow" class="eyebrow">{{ eyebrow }}</p>
            <h1 class="mt-1 font-display text-3xl font-semibold leading-tight tracking-[-0.02em] text-tabarak-ink md:text-4xl">{{ title }}</h1>
            <p v-if="description" class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 md:text-base">{{ description }}</p>
        </div>
        <div class="flex shrink-0 flex-wrap gap-2"><slot /></div>
    </div>
</template>
