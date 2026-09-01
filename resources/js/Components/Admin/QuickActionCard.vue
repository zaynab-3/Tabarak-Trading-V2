<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from '@lucide/vue';
import type { Component } from 'vue';

defineProps<{
    title: string;
    description: string;
    href: string;
    icon: Component;
    tone?: 'blue' | 'orange';
}>();
</script>

<template>
    <Link :href="href" class="group surface flex min-h-36 flex-col p-5 transition hover:-translate-y-0.5 hover:border-tabarak-blue hover:shadow-[0_14px_32px_rgba(64,88,225,0.12)]">
        <div class="flex items-start justify-between gap-4">
            <span class="grid size-11 place-items-center rounded-lg" :class="tone === 'orange' ? 'bg-[#FFF0E8] text-tabarak-orange' : 'bg-tabarak-mist text-tabarak-blue'">
                <component :is="icon" class="size-5" />
            </span>
            <ArrowRight class="size-4 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-tabarak-orange" />
        </div>
        <h3 class="mt-5 font-bold text-tabarak-ink">{{ title }}</h3>
        <p class="mt-1 text-sm leading-5 text-slate-500">{{ description }}</p>
    </Link>
</template>
