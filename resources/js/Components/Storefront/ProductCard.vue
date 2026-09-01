<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Image as ImageIcon } from '@lucide/vue';
import type { Product } from '@/types/catalogue';
import { productPackLabel } from '@/Utils/format';
import { formatMoney } from '@/Utils/format';
import AddToCartButton from '@/Components/Storefront/AddToCartButton.vue';

defineProps<{ product: Product }>();
</script>

<template>
    <article class="group grid min-h-[260px] overflow-hidden rounded-lg border border-tabarak-line bg-white transition hover:border-tabarak-blue hover:shadow-[0_16px_38px_rgba(64,88,225,0.12)] sm:grid-cols-[46%_54%]">
        <Link :href="route('products.show', product.slug)" class="relative flex min-h-52 items-center justify-center overflow-hidden bg-tabarak-mist p-5 sm:min-h-full">
            <img v-if="product.primary_image" :src="product.primary_image.url" :alt="product.primary_image.alt_text || product.name" class="h-full max-h-56 w-full object-contain" loading="lazy" />
            <div v-else class="flex h-full flex-col items-center justify-center gap-3 text-tabarak-blue">
                <ImageIcon class="size-8" />
                <span class="text-xs font-bold text-slate-500">Image coming soon</span>
            </div>
            <span v-if="product.is_featured" class="absolute left-3 top-3 rounded bg-tabarak-orange px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white">Featured</span>
        </Link>
        <div class="flex min-w-0 flex-col p-5">
            <div class="mb-3 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                <span>{{ product.brand?.name || 'Tabarak selection' }}</span><span aria-hidden="true">·</span><span>{{ product.category?.name || 'Catalogue' }}</span>
            </div>
            <h3 class="font-display text-xl font-bold leading-tight text-tabarak-ink lg:text-2xl">
                <Link :href="route('products.show', product.slug)">{{ product.name }}</Link>
            </h3>
            <p class="mt-3 text-sm text-slate-500">{{ productPackLabel(product) }}</p>
            <p v-if="product.unit_price" class="mt-2 text-lg font-bold text-tabarak-blue">{{ formatMoney(product.unit_price) }}</p>
            <div class="mt-auto flex flex-wrap items-center gap-2 border-t border-tabarak-line pt-4">
                <Link :href="route('products.show', product.slug)" class="flex min-h-11 flex-1 items-center justify-between text-sm font-bold text-tabarak-blue">
                    <span>View product</span><ArrowRight class="size-4 transition group-hover:translate-x-0.5" />
                </Link>
                <AddToCartButton v-if="product.unit_price" :product="product" compact />
            </div>
        </div>
    </article>
</template>
