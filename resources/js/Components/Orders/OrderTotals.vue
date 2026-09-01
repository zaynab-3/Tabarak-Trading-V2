<script setup lang="ts">
import { formatMoney } from '@/Utils/format';

defineProps<{ subtotal: string; total: string }>();
</script>

<template>
    <dl class="rounded-lg border border-tabarak-line bg-tabarak-mist p-5">
        <div class="flex items-center justify-between gap-4 text-sm text-slate-600"><dt>Subtotal</dt><dd>{{ formatMoney(subtotal) }}</dd></div>
        <div class="mt-4 flex items-center justify-between gap-4 border-t border-tabarak-line pt-4 text-xl font-bold text-tabarak-ink"><dt>Total USD</dt><dd class="text-tabarak-blue">{{ formatMoney(total) }}</dd></div>
    </dl>
</template>
