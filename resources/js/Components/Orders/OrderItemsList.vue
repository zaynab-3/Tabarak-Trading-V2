<script setup lang="ts">
import { Image as ImageIcon } from '@lucide/vue';
import type { OrderItem } from '@/types/orders';
import { formatMoney } from '@/Utils/format';

defineProps<{ items: OrderItem[] }>();
</script>

<template>
    <div class="divide-y divide-tabarak-line overflow-hidden rounded-lg border border-tabarak-line bg-white">
        <article v-for="item in items" :key="item.id" class="grid gap-4 p-4 sm:grid-cols-[96px_minmax(0,1fr)_auto] sm:items-center md:p-5">
            <div class="grid size-24 place-items-center overflow-hidden rounded-md bg-tabarak-mist p-2">
                <img v-if="item.image_url" :src="item.image_url" :alt="item.image_alt_text || item.product_name" class="h-full w-full object-contain" />
                <ImageIcon v-else class="size-7 text-slate-300" />
            </div>
            <div class="min-w-0">
                <h3 class="font-display text-lg font-bold leading-tight text-tabarak-ink">{{ item.product_name }}</h3>
                <p class="mt-1 text-xs text-slate-500">{{ item.product_sku || 'No SKU' }}<span v-if="item.pack_label"> · {{ item.pack_label }}</span></p>
                <p class="mt-2 text-sm text-slate-600">{{ item.quantity }} × {{ formatMoney(item.unit_price) }}</p>
            </div>
            <p class="text-lg font-bold text-tabarak-blue sm:text-right">{{ formatMoney(item.line_total) }}</p>
        </article>
    </div>
</template>
