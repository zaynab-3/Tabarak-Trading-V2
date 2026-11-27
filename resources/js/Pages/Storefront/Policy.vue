<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { PageProps } from '@/types';

const page = usePage<PageProps>();
</script>

<template>
    <Head title="Company policy" />
    <StorefrontLayout>
        <div class="page-shell py-8 sm:py-12 lg:py-16">
            <div class="mx-auto max-w-3xl">
                <Link :href="route('shop')" class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 transition hover:text-tabarak-ink"><ArrowLeft class="size-4" /> Back to catalogue</Link>

                <div class="mt-8">
                    <p class="eyebrow">Tabarak Trading LLC</p>
                    <h1 class="mt-2 font-display text-[clamp(2.5rem,7vw,5rem)] font-bold leading-[0.96] tracking-[-0.055em] text-tabarak-ink">Company policy</h1>
                    <p class="mt-5 max-w-2xl text-base leading-8 text-slate-600">These terms are shown in the catalogue and printed on invoices so customers can review them before and after ordering.</p>
                </div>

                <ol class="mt-10 space-y-8">
                    <li v-for="(line, index) in page.props.company_policy.lines" :key="`${index}-${line}`" class="grid grid-cols-[36px_minmax(0,1fr)] gap-4 sm:grid-cols-[44px_minmax(0,1fr)]">
                        <span class="font-display text-xl font-bold text-tabarak-orange sm:text-2xl">{{ String(index + 1).padStart(2, '0') }}</span>
                        <p class="text-base font-semibold leading-7 text-slate-700 sm:text-lg sm:leading-8">{{ line }}</p>
                    </li>
                </ol>

                <p class="mt-12 rounded-[22px] bg-[#F0EFEA] p-5 text-sm font-semibold leading-7 text-slate-600 sm:p-6">Bankruptcy-related collection and payment rights remain subject to applicable law. The policy does not attempt to override federal or state law.</p>
            </div>
        </div>
    </StorefrontLayout>
</template>
