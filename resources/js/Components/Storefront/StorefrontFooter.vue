<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import BrandMark from '@/Components/Shared/BrandMark.vue';
import type { PageProps } from '@/types';

const page = usePage<PageProps>();
</script>

<template>
    <footer class="mt-16 bg-[#171A1F] text-white sm:mt-20">
        <div class="page-shell grid gap-10 py-10 sm:py-12 md:grid-cols-[1.35fr_0.65fr] md:gap-16 lg:py-14">
            <div>
                <BrandMark inverse />
                <p class="mt-5 max-w-xl text-sm font-medium leading-7 text-white/58">{{ page.props.company_policy.summary }}</p>
                <Link :href="route('policy')" class="mt-4 inline-flex text-sm font-extrabold text-white underline decoration-tabarak-orange decoration-2 underline-offset-4 hover:text-[#FFD7C5]">Read company policy</Link>
            </div>

            <div class="grid grid-cols-2 gap-8 text-sm">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-white/35">Browse</p>
                    <div class="mt-4 grid gap-3 font-bold text-white/70">
                        <Link :href="route('shop')" class="transition hover:text-white">Catalogue</Link>
                        <Link :href="route('cart.index')" class="transition hover:text-white">Cart</Link>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-white/35">Information</p>
                    <div class="mt-4 grid gap-3 font-bold text-white/70">
                        <Link :href="route('policy')" class="transition hover:text-white">Company policy</Link>
                    </div>
                </div>
            </div>
        </div>
        <div class="page-shell pb-8 text-xs font-semibold text-white/30">© {{ new Date().getFullYear() }} Tabarak Trading LLC.</div>
    </footer>
</template>
