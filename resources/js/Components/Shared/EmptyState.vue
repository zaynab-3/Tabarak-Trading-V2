<script setup lang="ts">
import { PackageOpen } from '@lucide/vue';

defineProps<{ title: string; description: string }>();
</script>

<template>
    <div class="surface flex min-h-64 flex-col items-center justify-center px-6 py-12 text-center">
        <span class="mb-4 grid size-12 place-items-center rounded-full bg-oat-100 text-forest-700"><PackageOpen class="size-6" /></span>
        <h3 class="font-display text-xl font-bold text-forest-900">{{ title }}</h3>
        <p class="mt-2 max-w-md text-sm leading-6 text-slate-500">{{ description }}</p>
        <slot />
    </div>
</template>
