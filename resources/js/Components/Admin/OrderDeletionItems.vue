<script setup lang="ts">
import type { DeletedOrderItem } from '@/types/orders';
import { formatMoney } from '@/Utils/format';

defineProps<{ items: DeletedOrderItem[] }>();
</script>

<template>
    <div class="space-y-3">
        <article v-for="item in items" :key="item.id" class="surface grid gap-4 p-4 md:grid-cols-[112px_minmax(0,1fr)_auto] md:items-center md:p-5">
            <div class="grid min-h-24 place-items-center overflow-hidden rounded-md bg-tabarak-mist p-2"><img v-if="item.image_url" :src="item.image_url" :alt="item.image_alt_text || item.product_name" class="h-24 w-full object-contain" /><span v-else class="text-xs font-semibold text-slate-400">No image</span></div>
            <div class="min-w-0">
                <p class="font-display text-lg font-bold text-tabarak-ink">{{ item.product_name }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ [item.product_sku, item.pack_label].filter(Boolean).join(' · ') || 'No SKU or pack details' }}</p>
                <p class="mt-3 text-sm text-slate-600">{{ item.quantity }} × {{ formatMoney(item.unit_price) }}</p>
                <div v-if="item.stock_reserved" class="mt-3 flex flex-wrap gap-2 text-xs font-bold">
                    <span class="rounded bg-blue-50 px-2.5 py-1 text-tabarak-blue">{{ item.stock_reserved }} reserved</span>
                    <span v-if="item.stock_restored" class="rounded bg-emerald-50 px-2.5 py-1 text-emerald-700">{{ item.stock_restored }} returned to stock</span>
                    <span v-else class="rounded bg-[#FFF0E8] px-2.5 py-1 text-tabarak-orange">Stock remained deducted</span>
                </div>
                <p v-else class="mt-3 text-xs font-semibold text-slate-400">Open quantity or stock not tracked; no inventory adjustment.</p>
            </div>
            <p class="text-xl font-bold text-tabarak-blue md:text-right">{{ formatMoney(item.line_total) }}</p>
        </article>
    </div>
</template>
