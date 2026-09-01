<script setup lang="ts">
defineProps<{ eyebrow?: string; title: string; description?: string }>();
</script>

<template>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p v-if="eyebrow" class="eyebrow">{{ eyebrow }}</p>
            <h2 class="mt-2 font-display text-3xl font-bold tracking-tight text-tabarak-ink md:text-4xl">{{ title }}</h2>
            <p v-if="description" class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 md:text-base">{{ description }}</p>
        </div>
        <slot />
    </div>
</template>
