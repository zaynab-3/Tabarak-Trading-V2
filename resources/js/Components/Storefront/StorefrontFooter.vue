<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import BrandMark from '@/Components/Shared/BrandMark.vue';
</script>

<template>
    <footer class="mt-16 bg-tabarak-blue text-white">
        <div class="page-shell grid gap-10 py-12 md:grid-cols-[1.6fr_1fr]">
            <div><BrandMark inverse /><p class="mt-5 max-w-sm text-sm leading-6 text-white/75">A clear, dependable catalogue for wholesale food buyers and retail partners.</p></div>
            <div><p class="text-xs font-bold uppercase tracking-widest text-tabarak-orange">Catalogue</p><div class="mt-4 grid gap-2 text-sm text-white/80"><Link :href="route('shop')">Shop all products</Link></div></div>
        </div>
        <div class="border-t border-white/15 py-5 text-center text-xs text-white/70">© {{ new Date().getFullYear() }} Tabarak Trading.</div>
    </footer>
</template>
