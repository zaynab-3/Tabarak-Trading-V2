<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { ExternalLink, LogOut, Menu, UserRound } from '@lucide/vue';
import { computed } from 'vue';
import type { PageProps } from '@/types';

defineEmits<{ menu: [] }>();

const page = usePage<PageProps>();
const logout = () => router.post(route('admin.logout'));
const section = computed(() => {
    if (route().current('admin.products.*')) return 'Products';
    if (route().current('admin.categories.*')) return 'Categories';
    if (route().current('admin.brands.*')) return 'Brands';
    if (route().current('admin.media.*')) return 'Media library';
    if (route().current('admin.imports.*')) return 'Bulk import';
    if (route().current('admin.settings.*')) return 'Settings';
    return 'Dashboard';
});
</script>

<template>
    <header class="sticky top-0 z-40 flex min-h-[76px] items-center gap-3 border-b border-tabarak-line bg-white/95 px-4 backdrop-blur md:px-6 xl:px-8">
        <button class="admin-icon-button md:hidden" type="button" aria-label="Open admin navigation" @click="$emit('menu')">
            <Menu class="size-5" />
        </button>

        <div class="min-w-0">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-tabarak-orange">Tabarak administration</p>
            <p class="truncate text-base font-bold text-tabarak-ink">{{ section }}</p>
        </div>

        <div class="ml-auto flex items-center gap-2">
            <Link :href="route('shop')" class="admin-secondary-action">
                <span class="hidden sm:inline">View shop</span>
                <ExternalLink class="size-4" />
            </Link>
            <div class="hidden items-center gap-2 border-l border-tabarak-line pl-3 lg:flex">
                <span class="grid size-9 place-items-center rounded-full bg-tabarak-mist text-tabarak-blue"><UserRound class="size-4" /></span>
                <span class="max-w-40 truncate text-sm font-semibold text-slate-600">{{ page.props.auth.user?.name }}</span>
            </div>
            <button class="admin-icon-button hover:border-red-200 hover:text-red-600" type="button" aria-label="Sign out" @click="logout">
                <LogOut class="size-4" />
            </button>
        </div>
    </header>
</template>
