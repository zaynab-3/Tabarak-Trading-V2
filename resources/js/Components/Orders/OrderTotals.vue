<script setup lang="ts">
import { formatMoney } from '@/Utils/format';

defineProps<{ subtotal: string; total: string }>();
</script>

<template>
    <dl class="rounded-[22px] bg-white px-5 py-4 sm:px-5 sm:py-5">
        <div class="flex items-center justify-between gap-4 text-xs font-semibold text-slate-400">
            <dt>Subtotal</dt>
            <dd class="font-bold text-tabarak-ink">{{ formatMoney(subtotal) }}</dd>
        </div>
        <div class="mt-4 flex items-end justify-between gap-4">
            <dt class="text-sm font-extrabold text-tabarak-ink">Total USD</dt>
            <dd class="font-display text-[1.65rem] font-bold tracking-[-0.04em] text-tabarak-ink">{{ formatMoney(total) }}</dd>
        </div>
    </dl>
</template>
