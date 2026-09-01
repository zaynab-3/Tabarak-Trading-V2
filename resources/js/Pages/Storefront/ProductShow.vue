<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, BadgeCheck, Box, Layers3 } from '@lucide/vue';
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
        <div class="page-shell pb-8 pt-2 md:pb-10 md:pt-3">
            <Link :href="route('shop')" class="mb-3 inline-flex min-h-10 items-center gap-2 text-sm font-bold text-tabarak-blue transition hover:text-tabarak-orange">
                <ArrowLeft class="size-4" /> Back to catalogue
            </Link>

            <div class="grid gap-8 md:grid-cols-[0.95fr_1.05fr] lg:gap-14">
                <ProductGallery :images="product.images || []" :name="product.name" />

                <section class="md:py-3">
                    <div class="flex flex-wrap gap-2 text-xs font-bold uppercase tracking-wider text-tabarak-orange">
                        <span v-if="product.brand">{{ product.brand.name }}</span>
                        <span v-if="product.brand && product.category" aria-hidden="true">·</span>
                        <span v-if="product.category">{{ product.category.name }}</span>
                    </div>
                    <h1 class="mt-4 font-display text-4xl font-bold leading-tight text-tabarak-ink md:text-5xl">{{ product.name }}</h1>
                    <p v-if="product.short_description" class="mt-5 text-lg leading-8 text-slate-600">{{ product.short_description }}</p>

                    <div class="mt-7 grid grid-cols-2 gap-px overflow-hidden rounded-lg border border-tabarak-line bg-tabarak-line">
                        <div class="bg-white p-4">
                            <Box class="size-5 text-tabarak-blue" />
                            <span class="mt-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Format</span>
                            <strong class="mt-1 block text-sm text-tabarak-ink">{{ productPackLabel(product) }}</strong>
                        </div>
                        <div class="bg-white p-4">
                            <Layers3 class="size-5 text-tabarak-blue" />
                            <span class="mt-2 block text-xs font-bold uppercase tracking-wider text-slate-400">SKU</span>
                            <strong class="mt-1 block text-sm text-tabarak-ink">{{ product.sku || 'On request' }}</strong>
                        </div>
                    </div>

                    <ProductOrderPanel :product="product" />

                    <div v-if="!product.unit_price" class="mt-7 flex items-start gap-3 rounded-md border-l-4 border-tabarak-orange bg-[#FFF4EE] p-4">
                        <BadgeCheck class="mt-0.5 size-5 shrink-0 text-tabarak-orange" />
                        <p class="text-sm leading-6 text-slate-700">Wholesale catalogue listing. Contact Tabarak Trading for current availability and ordering details.</p>
                    </div>
                </section>
            </div>

            <section v-if="product.description" class="mt-12 border-t border-tabarak-line pt-8 md:mt-16">
                <h2 class="font-display text-2xl font-bold text-tabarak-ink">Product details</h2>
                <p class="mt-4 max-w-3xl whitespace-pre-line text-base leading-8 text-slate-600">{{ product.description }}</p>
            </section>

            <section v-if="relatedProducts.length" class="mt-14 border-t border-tabarak-line pt-10">
                <SectionHeading eyebrow="Keep browsing" title="Related products" />
                <div class="mt-7"><ProductGrid :products="relatedProducts" /></div>
            </section>
        </div>
    </StorefrontLayout>
</template>
