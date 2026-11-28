<script setup lang="ts">
defineProps<{ compact?: boolean; inverse?: boolean; storefront?: boolean }>();

// Reuse Laravel's deployment-aware icon URL from the document head.
const logoUrl = document.querySelector<HTMLLinkElement>('link[rel="apple-touch-icon"]')?.href;
</script>

<template>
    <div class="inline-flex items-center gap-2.5" :class="inverse ? 'text-white' : 'text-tabarak-ink'">
        <img
            :src="logoUrl"
            :alt="compact ? 'Tabarak Trading' : ''"
            class="size-9 shrink-0 object-contain sm:size-10"
        />
        <span v-if="!compact" class="leading-none">
            <span class="block font-display text-[17px] font-bold tracking-[-0.02em] sm:text-[19px]">Tabarak</span>
            <span class="mt-1 block text-[8px] font-extrabold uppercase tracking-[0.28em]" :class="inverse ? 'text-white/55' : 'text-tabarak-orange'">Trading</span>
        </span>
    </div>
</template>
