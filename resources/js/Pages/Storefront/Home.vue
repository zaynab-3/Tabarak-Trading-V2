<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, BadgeCheck, Boxes, PackageCheck, Search } from '@lucide/vue';
import CategoryCard from '@/Components/Storefront/CategoryCard.vue';
import ProductGrid from '@/Components/Storefront/ProductGrid.vue';
import SearchBar from '@/Components/Storefront/SearchBar.vue';
import SectionHeading from '@/Components/Storefront/SectionHeading.vue';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { Brand, Category, Product } from '@/types/catalogue';

defineProps<{ categories: Category[]; featuredProducts: Product[]; newProducts: Product[]; brands: Brand[]; catalogueIntro: string }>();
</script>

<template>
    <Head title="Wholesale food catalogue" />
    <StorefrontLayout>
        <section class="overflow-hidden bg-white">
            <div class="page-shell grid items-center gap-8 py-10 md:grid-cols-[1.1fr_0.9fr] md:py-14 lg:py-20">
                <div class="max-w-3xl">
                    <p class="eyebrow">Wholesale, made clearer</p>
                    <h1 class="mt-4 font-display text-5xl font-bold leading-[1.02] tracking-tight text-forest-900 md:text-6xl lg:text-7xl">A better way to browse your next bestsellers.</h1>
                    <p class="mt-5 max-w-2xl text-base leading-7 text-slate-600 md:text-lg">{{ catalogueIntro }}</p>
                    <div class="mt-7 max-w-2xl"><SearchBar /></div>
                    <div class="mt-6 flex flex-wrap gap-x-6 gap-y-3 text-sm font-semibold text-slate-600"><span class="inline-flex items-center gap-2"><BadgeCheck class="size-4 text-saffron-600" /> Curated range</span><span class="inline-flex items-center gap-2"><Boxes class="size-4 text-saffron-600" /> Wholesale formats</span><span class="inline-flex items-center gap-2"><PackageCheck class="size-4 text-saffron-600" /> Clear pack details</span></div>
                </div>
                <div class="relative min-h-[360px] overflow-hidden bg-forest-900 p-7 text-white md:min-h-[430px] md:p-9">
                    <div class="absolute -right-24 -top-24 size-72 rounded-full border border-white/10" /><div class="absolute -bottom-20 -left-20 size-64 rounded-full border border-saffron-400/30" />
                    <div class="relative flex h-full flex-col justify-between"><div><p class="text-xs font-bold uppercase tracking-[0.2em] text-saffron-400">Buyer’s shortcut</p><h2 class="mt-4 max-w-sm font-display text-4xl font-bold leading-tight">Start with the shelf, then find the right pack.</h2></div><div class="mt-12 grid grid-cols-2 gap-px bg-white/15"><Link :href="route('categories.index')" class="bg-forest-900 p-5 transition hover:bg-forest-800"><span class="text-3xl font-bold">{{ categories.length }}+</span><span class="mt-2 block text-sm text-forest-100">Core categories</span></Link><Link :href="route('shop')" class="bg-forest-900 p-5 transition hover:bg-forest-800"><Search class="size-7" /><span class="mt-2 block text-sm text-forest-100">Search catalogue</span></Link></div></div>
                </div>
            </div>
        </section>

        <section class="page-shell py-12 md:py-16"><SectionHeading eyebrow="Shop by aisle" title="Core categories" description="Move quickly from category to pack-ready product lines."><Link :href="route('categories.index')" class="btn-secondary">View all <ArrowRight class="size-4" /></Link></SectionHeading><div class="mt-7 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4"><CategoryCard v-for="category in categories" :key="category.id" :category="category" /></div></section>
        <section class="border-y border-oat-200 bg-white"><div class="page-shell py-12 md:py-16"><SectionHeading eyebrow="Buyer favourites" title="Featured products" /><div class="mt-7"><ProductGrid :products="featuredProducts" /></div></div></section>
        <section class="page-shell py-12 md:py-16"><SectionHeading eyebrow="Fresh to the list" title="New arrivals"><Link :href="route('shop')" class="btn-secondary">Full catalogue <ArrowRight class="size-4" /></Link></SectionHeading><div class="mt-7"><ProductGrid :products="newProducts" /></div></section>
        <section class="bg-forest-800 py-12 text-white md:py-14"><div class="page-shell"><p class="text-center text-xs font-bold uppercase tracking-[0.2em] text-saffron-400">Brands in the catalogue</p><div class="mt-7 flex flex-wrap justify-center gap-3"><Link v-for="brand in brands" :key="brand.id" :href="route('brands.show', brand.slug)" class="border border-white/15 bg-white/5 px-5 py-3 text-sm font-bold transition hover:bg-white/10">{{ brand.name }}</Link></div></div></section>
    </StorefrontLayout>
</template>
