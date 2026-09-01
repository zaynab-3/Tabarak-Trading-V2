<script setup lang="ts">
defineProps<{ eyebrow?: string; title: string; description?: string }>();
</script>

<template>
    <div class="mb-6 flex flex-col gap-4 border-b border-tabarak-line pb-5 md:flex-row md:items-end md:justify-between">
        <div class="min-w-0">
            <p v-if="eyebrow" class="eyebrow">{{ eyebrow }}</p>
            <h1 class="mt-1 font-display text-3xl font-bold leading-tight text-tabarak-ink md:text-4xl">{{ title }}</h1>
            <p v-if="description" class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">{{ description }}</p>
        </div>
        <div class="flex shrink-0 flex-wrap gap-2"><slot /></div>
    </div>
</template>
