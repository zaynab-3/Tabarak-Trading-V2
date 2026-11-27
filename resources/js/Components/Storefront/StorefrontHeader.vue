<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Search } from '@lucide/vue';
import { ref } from 'vue';
import BrandMark from '@/Components/Shared/BrandMark.vue';
import CartLink from '@/Components/Storefront/CartLink.vue';
import StorefrontSearchDialog from '@/Components/Storefront/StorefrontSearchDialog.vue';

const searchOpen = ref(false);
</script>

<template>
    <header class="sticky top-0 z-50 bg-white/95 text-tabarak-ink shadow-[0_1px_0_rgba(25,27,32,0.07)] backdrop-blur-md">
        <div class="page-shell flex h-16 items-center gap-4 sm:h-[72px]">
            <Link :href="route('shop')" aria-label="Tabarak Trading catalogue" class="shrink-0">
                <BrandMark storefront />
            </Link>

            <nav class="ml-8 hidden items-center gap-7 text-[13px] font-bold text-slate-500 lg:flex" aria-label="Store navigation">
                <Link :href="route('shop')" class="transition hover:text-tabarak-ink" :class="route().current('shop') || route().current('products.show') ? 'text-tabarak-ink' : ''">Catalogue</Link>
                <Link :href="route('policy')" class="transition hover:text-tabarak-ink" :class="route().current('policy') ? 'text-tabarak-ink' : ''">Company policy</Link>
            </nav>

            <div class="ml-auto flex items-center gap-1 sm:gap-2">
                <button
                    type="button"
                    class="grid size-11 place-items-center rounded-full text-tabarak-ink transition hover:bg-[#F1EFEA]"
                    aria-label="Search catalogue"
                    title="Search"
                    @click="searchOpen = true"
                >
                    <Search class="size-[21px]" />
                </button>
                <CartLink />
            </div>
        </div>
    </header>

    <StorefrontSearchDialog :open="searchOpen" @close="searchOpen = false" />
</template>
