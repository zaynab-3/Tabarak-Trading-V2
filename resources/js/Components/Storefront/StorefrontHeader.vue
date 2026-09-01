<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ShoppingCart } from '@lucide/vue';
import type { PageProps } from '@/types';
import BrandMark from '@/Components/Shared/BrandMark.vue';
import SearchBar from '@/Components/Storefront/SearchBar.vue';
import { useAutoHideStorefrontHeader } from '@/Composables/useAutoHideStorefrontHeader';

const { headerVisible } = useAutoHideStorefrontHeader();
const page = usePage<PageProps>();
</script>

<template>
    <header
        class="sticky top-0 z-50 bg-tabarak-blue text-white shadow-[0_1px_0_rgba(255,255,255,0.18)] transition-transform duration-200 ease-out will-change-transform"
        :class="headerVisible ? 'translate-y-0' : '-translate-y-full'"
    >
        <div class="page-shell flex min-h-16 flex-wrap items-center gap-3 py-2 md:flex-nowrap md:gap-5 md:py-0">
            <Link :href="route('shop')" aria-label="Tabarak Trading shop" class="shrink-0"><BrandMark inverse /></Link>
            <div class="order-3 w-full md:order-none md:mx-auto md:max-w-2xl"><SearchBar /></div>
            <div class="ml-auto flex items-center gap-1">
                <Link :href="route('shop')" class="relative inline-flex min-h-10 items-center px-3 text-sm font-bold text-white after:absolute after:inset-x-3 after:bottom-0 after:h-0.5 after:bg-tabarak-orange">Shop</Link>
                <Link :href="route('cart.index')" class="relative inline-flex min-h-10 items-center gap-2 rounded-md px-3 text-sm font-bold text-white transition hover:bg-white/10" aria-label="Open cart">
                    <ShoppingCart class="size-5" />
                    <span class="hidden sm:inline">Cart</span>
                    <span v-if="page.props.cart.item_count" class="grid min-w-5 place-items-center rounded-full bg-tabarak-orange px-1.5 py-0.5 text-[10px] leading-4 text-white">{{ page.props.cart.item_count }}</span>
                </Link>
            </div>
        </div>
    </header>
</template>
