<script setup lang="ts">
import { Wheat } from '@lucide/vue';

defineProps<{ compact?: boolean; inverse?: boolean; storefront?: boolean }>();
</script>

<template>
    <div class="inline-flex items-center gap-3" :class="inverse ? 'text-white' : storefront ? 'text-tabarak-blue' : 'text-forest-900'">
        <span class="grid size-10 shrink-0 place-items-center rounded-full border" :class="inverse ? 'border-white/30 bg-white/10 text-white' : storefront ? 'border-tabarak-line bg-tabarak-mist text-tabarak-blue' : 'border-saffron-500/40 bg-saffron-100'">
            <Wheat class="size-5" aria-hidden="true" />
        </span>
        <span v-if="!compact" class="leading-none">
            <span class="block font-display text-xl font-bold tracking-tight">Tabarak</span>
            <span class="mt-1 block text-[10px] font-bold uppercase tracking-[0.28em]" :class="inverse ? 'text-white/75' : storefront ? 'text-tabarak-orange' : 'text-saffron-600'">Trading</span>
        </span>
    </div>
</template>
