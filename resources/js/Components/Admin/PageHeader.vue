<script setup lang="ts">
defineProps<{ eyebrow?: string; title: string; description?: string }>();
</script>

<template>
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div><p v-if="eyebrow" class="eyebrow">{{ eyebrow }}</p><h1 class="mt-1 font-display text-3xl font-bold text-forest-900">{{ title }}</h1><p v-if="description" class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">{{ description }}</p></div>
        <div class="flex flex-wrap gap-2"><slot /></div>
    </div>
</template>
