<script setup lang="ts">
import { PackageOpen } from '@lucide/vue';

defineProps<{ title: string; description: string }>();
</script>

<template>
    <div class="flex min-h-64 flex-col items-center justify-center px-6 py-12 text-center">
        <span class="mb-4 grid size-14 place-items-center rounded-full bg-[#ECEAE5] text-tabarak-ink"><PackageOpen class="size-6" /></span>
        <h3 class="font-display text-xl font-bold tracking-[-0.02em] text-tabarak-ink">{{ title }}</h3>
        <p class="mt-2 max-w-md text-sm leading-6 text-slate-500">{{ description }}</p>
        <slot />
    </div>
</template>
