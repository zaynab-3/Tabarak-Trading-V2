<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import BrandMark from '@/Components/Shared/BrandMark.vue';
</script>

<template>
    <footer class="mt-16 border-t border-[#E7EAF3] bg-white text-tabarak-ink">
        <div class="page-shell grid gap-10 py-10 md:grid-cols-[1.6fr_1fr]">
            <div><BrandMark /><p class="mt-4 max-w-sm text-sm leading-6 text-slate-500">A clear, dependable catalogue for wholesale food buyers and retail partners.</p></div>
            <div><p class="text-sm font-semibold text-tabarak-ink">Catalogue</p><div class="mt-3 grid gap-2 text-sm text-slate-500"><Link :href="route('shop')" class="hover:text-tabarak-blue">Shop all products</Link></div></div>
        </div>
        <div class="border-t border-[#EEF0F6] py-5 text-center text-xs text-slate-400">© {{ new Date().getFullYear() }} Tabarak Trading.</div>
    </footer>
</template>
