<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import BrandMark from '@/Components/Shared/BrandMark.vue';
</script>

<template>
    <div class="grid min-h-screen bg-[#F7F8FC] md:grid-cols-[0.9fr_1.1fr]">
        <section class="hidden bg-tabarak-blue p-10 text-white md:flex md:flex-col md:justify-between lg:p-14">
            <BrandMark inverse />
            <div class="max-w-xl">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#FFC2A5]">Catalogue control</p>
                <h1 class="mt-4 font-display text-5xl font-bold leading-tight">A clear workspace for every catalogue task.</h1>
                <p class="mt-5 max-w-lg text-base leading-7 text-white/75">Products, categories, media, and import drafts organized for fast work on tablet and desktop.</p>
                <span class="mt-8 block h-1 w-16 rounded-full bg-tabarak-orange" />
            </div>
            <p class="text-xs font-semibold text-white/60">Protected Tabarak Trading administration</p>
        </section>
        <main class="flex items-center justify-center p-5 md:p-10">
            <div class="w-full max-w-md">
                <div class="mb-8 md:hidden"><BrandMark storefront /></div>
                <slot />
                <p class="mt-6 text-center text-sm text-slate-500">
                    <Link :href="route('shop')" class="inline-flex min-h-11 items-center gap-2 font-semibold text-tabarak-blue hover:text-tabarak-orange">
                        <ArrowLeft class="size-4" /> Return to shop
                    </Link>
                </p>
            </div>
        </main>
    </div>
</template>
