<script setup lang="ts">
defineProps<{ eyebrow: string; title: string; description?: string | null; count?: number }>();
</script>

<template>
    <section class="border-b border-oat-200 bg-white">
        <div class="page-shell py-10 md:py-14">
            <p class="eyebrow">{{ eyebrow }}</p>
            <div class="mt-3 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div><h1 class="max-w-3xl font-display text-4xl font-bold tracking-tight text-forest-900 md:text-5xl">{{ title }}</h1><p v-if="description" class="mt-4 max-w-2xl text-base leading-7 text-slate-600">{{ description }}</p></div>
                <p v-if="count !== undefined" class="shrink-0 text-sm font-semibold text-slate-500">{{ count }} product{{ count === 1 ? '' : 's' }}</p>
            </div>
        </div>
    </section>
</template>
