<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Menu, X } from '@lucide/vue';
import { ref } from 'vue';
import BrandMark from '@/Components/Shared/BrandMark.vue';
import SearchBar from '@/Components/Storefront/SearchBar.vue';

const open = ref(false);
const links = [
    { label: 'Home', routeName: 'home' },
    { label: 'Shop', routeName: 'shop' },
    { label: 'Categories', routeName: 'categories.index' },
    { label: 'Brands', routeName: 'brands.index' },
];
</script>

<template>
    <header class="sticky top-0 z-40 border-b border-oat-200 bg-white/95 backdrop-blur">
        <div class="border-b border-oat-200 bg-forest-800 py-2 text-center text-[11px] font-bold uppercase tracking-[0.16em] text-forest-50">
            Wholesale catalogue · Lebanon
        </div>
        <div class="page-shell flex min-h-20 items-center gap-5">
            <Link :href="route('home')" aria-label="Tabarak Trading home"><BrandMark /></Link>
            <nav class="ml-auto hidden items-center gap-1 md:flex" aria-label="Primary navigation">
                <Link v-for="link in links" :key="link.routeName" :href="route(link.routeName)" class="rounded-md px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-oat-100 hover:text-forest-900" :class="route().current(link.routeName) ? 'bg-oat-100 text-forest-900' : ''">{{ link.label }}</Link>
            </nav>
            <button class="ml-auto grid size-11 place-items-center rounded-md border border-oat-300 text-forest-900 md:hidden" type="button" :aria-expanded="open" aria-label="Toggle navigation" @click="open = !open">
                <X v-if="open" class="size-5" /><Menu v-else class="size-5" />
            </button>
        </div>
        <div class="page-shell pb-4 md:hidden"><SearchBar /></div>
        <nav v-if="open" class="page-shell grid gap-1 border-t border-oat-200 py-3 md:hidden" aria-label="Mobile navigation">
            <Link v-for="link in links" :key="link.routeName" :href="route(link.routeName)" class="rounded-md px-3 py-3 text-sm font-semibold text-slate-700" @click="open = false">{{ link.label }}</Link>
        </nav>
    </header>
</template>
