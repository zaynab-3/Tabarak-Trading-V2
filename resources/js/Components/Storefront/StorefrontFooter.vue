<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import BrandMark from '@/Components/Shared/BrandMark.vue';
</script>

<template>
    <footer class="mt-20 bg-forest-900 text-white">
        <div class="page-shell grid gap-10 py-12 md:grid-cols-[1.4fr_1fr_1fr]">
            <div><BrandMark inverse /><p class="mt-5 max-w-sm text-sm leading-6 text-forest-100">A clear, dependable catalogue for wholesale food buyers and retail partners.</p></div>
            <div><p class="text-xs font-bold uppercase tracking-widest text-saffron-400">Catalogue</p><div class="mt-4 grid gap-2 text-sm text-forest-100"><Link :href="route('shop')">All products</Link><Link :href="route('categories.index')">Categories</Link><Link :href="route('brands.index')">Brands</Link></div></div>
            <div><p class="text-xs font-bold uppercase tracking-widest text-saffron-400">Administration</p><div class="mt-4 text-sm text-forest-100"><Link :href="route('admin.login')">Admin sign in</Link></div></div>
        </div>
        <div class="border-t border-white/10 py-5 text-center text-xs text-forest-100">© {{ new Date().getFullYear() }} Tabarak Trading. Catalogue foundation.</div>
    </footer>
</template>
