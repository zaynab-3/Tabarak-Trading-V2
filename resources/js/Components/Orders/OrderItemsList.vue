<script setup lang="ts">
import { Image as ImageIcon } from '@lucide/vue';
import type { OrderItem } from '@/types/orders';
import { formatMoney } from '@/Utils/format';

defineProps<{ items: OrderItem[] }>();
</script>

<template>
    <div class="divide-y divide-[#E3E0D9]">
        <article v-for="item in items" :key="item.id" class="grid gap-4 py-5 sm:grid-cols-[88px_minmax(0,1fr)_auto] sm:items-center sm:py-6">
            <div class="grid size-[88px] place-items-center overflow-hidden rounded-[18px] bg-[#F0EFEA] p-2.5">
                <img v-if="item.image_url" :src="item.image_url" :alt="item.image_alt_text || item.product_name" class="max-h-full max-w-full object-contain" />
                <ImageIcon v-else class="size-7 text-slate-300" />
            </div>
            <div class="min-w-0">
                <h3 class="font-display text-lg font-bold leading-tight tracking-[-0.02em] text-tabarak-ink">{{ item.product_name }}</h3>
                <p class="mt-1 text-xs font-semibold text-slate-400">{{ item.product_sku || 'No SKU' }}<span v-if="item.pack_label"> · {{ item.pack_label }}</span></p>
                <p class="mt-2 text-sm font-semibold text-slate-500">{{ item.quantity }} × {{ formatMoney(item.unit_price) }}</p>
            </div>
            <p class="font-display text-lg font-bold tracking-[-0.02em] text-tabarak-ink sm:text-right">{{ formatMoney(item.line_total) }}</p>
        </article>
    </div>
</template>
