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
        class="sticky top-0 z-50 border-b border-[#E7EAF3] bg-white/95 text-tabarak-ink backdrop-blur transition-transform duration-200 ease-out will-change-transform"
        :class="headerVisible ? 'translate-y-0' : '-translate-y-full'"
    >
        <div class="page-shell flex min-h-12 items-center justify-between gap-3 py-1.5 sm:min-h-14 md:min-h-16 md:gap-5 md:py-0">
            <Link :href="route('shop')" aria-label="Tabarak Trading shop" class="shrink-0"><BrandMark storefront /></Link>
            <div class="hidden w-full md:mx-auto md:block md:max-w-2xl"><SearchBar /></div>
            <div class="flex items-center gap-1 sm:gap-2">
                <Link :href="route('shop')" class="relative inline-flex min-h-8 items-center px-2.5 text-xs font-semibold text-tabarak-blue sm:min-h-10 sm:px-3 sm:text-sm after:absolute after:inset-x-2.5 after:bottom-0 after:h-0.5 after:bg-tabarak-orange">Shop</Link>
                <Link :href="route('cart.index')" class="relative inline-flex min-h-8 items-center gap-1.5 rounded-lg px-2.5 text-xs font-semibold text-tabarak-ink transition hover:bg-tabarak-mist sm:min-h-10 sm:gap-2 sm:px-3 sm:text-sm" aria-label="Open cart">
                    <ShoppingCart class="size-4 sm:size-5" />
                    <span class="hidden sm:inline">Cart</span>
                    <span v-if="page.props.cart.item_count" class="grid min-w-4 place-items-center rounded-full bg-tabarak-orange px-1 py-0.5 text-[9px] font-bold leading-3 text-white sm:min-w-5 sm:px-1.5 sm:text-[10px] sm:leading-4">{{ page.props.cart.item_count }}</span>
                </Link>
            </div>
        </div>
        <div class="page-shell pb-2 md:hidden">
            <SearchBar compact />
        </div>
    </header>
</template>
