<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import BrandMark from '@/Components/Shared/BrandMark.vue';
</script>

<template>
    <div class="grid min-h-screen bg-oat-50 md:grid-cols-[0.9fr_1.1fr]">
        <section class="hidden bg-forest-900 p-10 text-white md:flex md:flex-col md:justify-between lg:p-14">
            <BrandMark inverse />
            <div class="max-w-xl"><p class="eyebrow text-saffron-400">Catalogue control</p><h1 class="mt-4 font-display text-5xl font-bold leading-tight">A focused workspace for a growing wholesale range.</h1><p class="mt-5 text-base leading-7 text-forest-100">Products, categories, brands, media and import drafts—organized for fast review on tablet and desktop.</p></div>
            <p class="text-xs text-forest-100">Protected administration · Tabarak Trading V2</p>
        </section>
        <main class="flex items-center justify-center p-5 md:p-10"><div class="w-full max-w-md"><div class="mb-8 md:hidden"><BrandMark /></div><slot /><p class="mt-6 text-center text-sm text-slate-500"><Link :href="route('shop')" class="font-semibold text-forest-700">Return to shop</Link></p></div></main>
    </div>
</template>
