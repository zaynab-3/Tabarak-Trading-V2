<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ShoppingBag } from '@lucide/vue';
import type { PageProps } from '@/types';

const page = usePage<PageProps>();
</script>

<template>
    <Link
        :href="route('cart.index')"
        class="relative grid size-11 shrink-0 place-items-center rounded-full text-tabarak-ink transition hover:bg-[#F1EFEA]"
        aria-label="Open shopping cart"
        title="Shopping cart"
    >
        <ShoppingBag class="size-[21px]" aria-hidden="true" />
        <span
            v-if="page.props.cart.item_count"
            class="absolute right-0.5 top-0.5 inline-flex min-w-[18px] items-center justify-center rounded-full bg-tabarak-orange px-1 text-[10px] font-extrabold leading-[18px] text-white"
        >{{ page.props.cart.item_count }}</span>
    </Link>
</template>
