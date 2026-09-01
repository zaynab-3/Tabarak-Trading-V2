<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import BrandMark from '@/Components/Shared/BrandMark.vue';
import SearchBar from '@/Components/Storefront/SearchBar.vue';
</script>

<template>
    <header class="sticky top-0 z-50 bg-tabarak-blue text-white shadow-[0_1px_0_rgba(255,255,255,0.18)]">
        <div class="page-shell flex min-h-16 flex-wrap items-center gap-3 py-2 md:flex-nowrap md:gap-5 md:py-0">
            <Link :href="route('shop')" aria-label="Tabarak Trading shop" class="shrink-0"><BrandMark inverse /></Link>
            <div class="order-3 w-full md:order-none md:mx-auto md:max-w-2xl"><SearchBar /></div>
            <Link :href="route('shop')" class="relative ml-auto inline-flex min-h-10 items-center px-3 text-sm font-bold text-white after:absolute after:inset-x-3 after:bottom-0 after:h-0.5 after:bg-tabarak-orange">Shop</Link>
        </div>
    </header>
</template>
