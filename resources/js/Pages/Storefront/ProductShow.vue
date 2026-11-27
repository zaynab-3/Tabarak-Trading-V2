<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, BadgeCheck } from '@lucide/vue';
import ProductGallery from '@/Components/Storefront/ProductGallery.vue';
import ProductOrderPanel from '@/Components/Storefront/ProductOrderPanel.vue';
import ProductGrid from '@/Components/Storefront/ProductGrid.vue';
import SectionHeading from '@/Components/Storefront/SectionHeading.vue';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { Product } from '@/types/catalogue';
import { productPackLabel } from '@/Utils/format';

defineProps<{ product: Product; relatedProducts: Product[] }>();
</script>

<template>
    <Head :title="product.name" />
    <StorefrontLayout>
        <div class="page-shell pb-12 pt-5 sm:pt-7 lg:pb-16">
            <Link :href="route('shop')" class="inline-flex min-h-10 items-center gap-2 text-sm font-bold text-slate-500 transition hover:text-tabarak-ink">
                <ArrowLeft class="size-4" /> Back to catalogue
            </Link>

            <div class="mt-4 grid items-start gap-8 md:grid-cols-[minmax(0,1fr)_minmax(0,0.9fr)] md:gap-10 lg:mt-6 lg:gap-16">
                <ProductGallery :images="product.images || []" :name="product.name" />

                <section class="md:pt-5 lg:pt-8">
                    <div class="flex flex-wrap gap-2 text-[10px] font-extrabold uppercase tracking-[0.14em] text-tabarak-orange sm:text-xs">
                        <span v-if="product.brand">{{ product.brand.name }}</span>
                        <span v-if="product.brand && product.category" class="text-slate-300" aria-hidden="true">/</span>
                        <span v-if="product.category">{{ product.category.name }}</span>
                    </div>
                    <h1 class="mt-3 max-w-2xl font-display text-[clamp(2rem,5vw,4rem)] font-bold leading-[1.02] tracking-[-0.05em] text-tabarak-ink">{{ product.name }}</h1>
                    <p class="mt-3 text-sm font-semibold text-slate-400">{{ productPackLabel(product) }}</p>
                    <p v-if="product.short_description" class="mt-5 max-w-xl text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">{{ product.short_description }}</p>

                    <ProductOrderPanel :product="product" />

                    <div v-if="!product.unit_price" class="mt-7 flex items-start gap-3 rounded-[20px] bg-[#F8ECE5] p-4">
                        <BadgeCheck class="mt-0.5 size-5 shrink-0 text-tabarak-orange" />
                        <p class="text-sm leading-6 text-slate-700">Contact Tabarak Trading for current availability and pricing.</p>
                    </div>
                </section>
            </div>

            <section v-if="product.description" class="mt-12 max-w-3xl md:mt-16">
                <p class="eyebrow">About this product</p>
                <h2 class="mt-2 font-display text-2xl font-bold tracking-[-0.03em] text-tabarak-ink sm:text-3xl">Product details</h2>
                <p class="mt-4 whitespace-pre-line text-base leading-8 text-slate-600">{{ product.description }}</p>
            </section>

            <section v-if="relatedProducts.length" class="mt-14 md:mt-20">
                <SectionHeading eyebrow="Keep browsing" title="Related products" />
                <div class="mt-7"><ProductGrid :products="relatedProducts" /></div>
            </section>
        </div>
    </StorefrontLayout>
</template>
