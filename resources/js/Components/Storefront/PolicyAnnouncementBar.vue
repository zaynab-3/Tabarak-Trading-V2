<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import type { PageProps } from '@/types';

const page = usePage<PageProps>();
</script>

<template>
    <div class="bg-[#F8ECE5] text-tabarak-ink">
        <div class="page-shell flex min-h-10 items-center gap-3 py-2 text-[11px] sm:text-xs">
            <span class="size-1.5 shrink-0 rounded-full bg-tabarak-orange"></span>
            <span class="min-w-0 flex-1 truncate font-semibold text-slate-600">{{ page.props.company_policy.summary }}</span>
            <Link :href="route('policy')" class="shrink-0 font-extrabold text-tabarak-ink underline decoration-tabarak-orange/50 underline-offset-4 hover:decoration-tabarak-orange">Policy</Link>
        </div>
    </div>
</template>
