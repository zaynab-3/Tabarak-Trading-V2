<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Image as ImageIcon } from '@lucide/vue';
import type { Product } from '@/types/catalogue';
import { formatMoney, productPackLabel } from '@/Utils/format';
import AddToCartButton from '@/Components/Storefront/AddToCartButton.vue';

defineProps<{ product: Product }>();
</script>

<template>
    <article class="group min-w-0">
        <Link :href="route('products.show', product.slug)" class="relative grid aspect-square place-items-center overflow-hidden rounded-[22px] bg-[#F0EFEA] sm:rounded-[26px]">
            <img
                v-if="product.primary_image"
                :src="product.primary_image.url"
                :alt="product.primary_image.alt_text || product.name"
                class="max-h-[88%] max-w-[88%] object-contain transition duration-300 ease-out group-hover:scale-[1.035]"
                loading="lazy"
            />
            <div v-else class="flex flex-col items-center justify-center gap-2 text-slate-400">
                <ImageIcon class="size-7" />
                <span class="text-[10px] font-bold">Image coming soon</span>
            </div>
            <span v-if="product.is_featured" class="absolute left-2.5 top-2.5 rounded-full bg-white/95 px-2.5 py-1 text-[9px] font-extrabold uppercase tracking-[0.12em] text-tabarak-ink sm:left-3 sm:top-3">Featured</span>
        </Link>

        <div class="px-0.5 pt-3">
            <p class="truncate text-[9px] font-extrabold uppercase tracking-[0.13em] text-tabarak-orange sm:text-[10px]">{{ product.brand?.name || 'Tabarak selection' }}</p>
            <h3 class="mt-1 line-clamp-2 min-h-[2.5rem] text-[13px] font-bold leading-[1.35] text-tabarak-ink sm:text-[15px]">
                <Link :href="route('products.show', product.slug)" class="transition hover:text-tabarak-orange">{{ product.name }}</Link>
            </h3>
            <p class="mt-1 truncate text-[10px] font-medium text-slate-400 sm:text-xs">{{ productPackLabel(product) }}</p>

            <div class="mt-2.5 flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p v-if="product.unit_price" class="text-base font-extrabold tracking-[-0.02em] text-tabarak-ink sm:text-lg">{{ formatMoney(product.unit_price) }}</p>
                    <span v-else class="text-xs font-bold text-slate-400">Ask for price</span>
                </div>
                <AddToCartButton v-if="product.unit_price && product.is_available" :product="product" compact />
                <span v-else-if="product.unit_price" class="text-[10px] font-bold text-slate-400">Out of stock</span>
            </div>
        </div>
    </article>
</template>
