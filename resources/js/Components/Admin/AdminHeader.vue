<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { ExternalLink, LogOut, Menu } from '@lucide/vue';
import type { PageProps } from '@/types';

defineEmits<{ menu: [] }>();
const page = usePage<PageProps>();
const logout = () => router.post(route('admin.logout'));
</script>

<template>
    <header class="flex min-h-16 items-center gap-3 border-b border-oat-200 bg-white px-4 md:px-6">
        <button class="grid size-10 place-items-center rounded-md border border-oat-300 text-forest-900 md:hidden" type="button" aria-label="Open admin navigation" @click="$emit('menu')"><Menu class="size-5" /></button>
        <div><p class="text-xs font-bold uppercase tracking-wider text-slate-400">Workspace</p><p class="text-sm font-semibold text-forest-900">Tabarak catalogue</p></div>
        <div class="ml-auto flex items-center gap-2">
            <Link :href="route('shop')" class="hidden min-h-10 items-center gap-2 rounded-md border border-oat-300 px-3 text-sm font-semibold text-slate-600 sm:inline-flex">View shop <ExternalLink class="size-4" /></Link>
            <span class="hidden text-sm text-slate-500 lg:inline">{{ page.props.auth.user?.name }}</span>
            <button class="grid size-10 place-items-center rounded-md border border-oat-300 text-slate-600 hover:text-red-700" type="button" aria-label="Sign out" @click="logout"><LogOut class="size-4" /></button>
        </div>
    </header>
</template>
