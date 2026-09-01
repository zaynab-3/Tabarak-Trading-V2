<script setup lang="ts">
defineProps<{ compact?: boolean; inverse?: boolean; storefront?: boolean }>();
</script>

<template>
    <div class="inline-flex items-center gap-2.5" :class="inverse ? 'text-white' : storefront ? 'text-tabarak-blue' : 'text-forest-900'">
        <span class="grid size-11 shrink-0 place-items-center overflow-hidden rounded-lg bg-white p-1 shadow-sm">
            <img src="/icons/apple-touch-icon.png" :alt="compact ? 'Tabarak Trading' : ''" class="h-full w-full object-contain" />
        </span>
        <span v-if="!compact" class="leading-none">
            <span class="block font-display text-xl font-bold tracking-tight">Tabarak</span>
            <span class="mt-1 block text-[10px] font-bold uppercase tracking-[0.28em]" :class="inverse ? 'text-white/75' : storefront ? 'text-tabarak-orange' : 'text-saffron-600'">Trading</span>
        </span>
    </div>
</template>
