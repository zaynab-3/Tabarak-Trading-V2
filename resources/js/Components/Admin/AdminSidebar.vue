<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BellRing, Boxes, Building2, ClipboardList, FolderTree, Gauge, Images, Settings, Sparkles, Store } from '@lucide/vue';
import type { Component } from 'vue';
import BrandMark from '@/Components/Shared/BrandMark.vue';

defineProps<{ mobile?: boolean }>();

interface NavigationItem {
    label: string;
    routeName: string;
    icon: Component;
}

interface NavigationGroup {
    label: string;
    items: NavigationItem[];
}

const dashboard: NavigationItem = { label: 'Dashboard', routeName: 'admin.dashboard', icon: Gauge };
const groups: NavigationGroup[] = [
    {
        label: 'Catalogue',
        items: [
            { label: 'Products', routeName: 'admin.products.index', icon: Boxes },
            { label: 'Categories', routeName: 'admin.categories.index', icon: FolderTree },
            { label: 'Brands', routeName: 'admin.brands.index', icon: Building2 },
        ],
    },
    {
        label: 'Sales',
        items: [
            { label: 'Orders', routeName: 'admin.orders.index', icon: ClipboardList },
            { label: 'Order notices', routeName: 'admin.order-notices.index', icon: BellRing },
        ],
    },
    {
        label: 'Content',
        items: [
            { label: 'Media', routeName: 'admin.media.index', icon: Images },
            { label: 'Bulk import', routeName: 'admin.imports.index', icon: Sparkles },
        ],
    },
    {
        label: 'Workspace',
        items: [{ label: 'Settings', routeName: 'admin.settings.index', icon: Settings }],
    },
];

const isActive = (routeName: string) => route().current(routeName)
    || route().current(`${routeName.split('.').slice(0, 2).join('.')}.*`);
</script>

<template>
    <aside class="flex h-full w-[236px] flex-col border-r border-[#E7EAF3] bg-white text-tabarak-ink" :class="mobile ? 'w-[276px]' : ''">
        <div class="flex min-h-[72px] items-center border-b border-[#EEF0F6] px-5">
            <BrandMark />
            <template v-if="false"><BrandMark compact /></template>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-5" aria-label="Admin navigation">
            <Link
                :href="route(dashboard.routeName)"
                class="admin-nav-link"
                :class="isActive(dashboard.routeName) ? 'admin-nav-link-active' : ''"
                :title="dashboard.label"
            >
                <component :is="dashboard.icon" class="size-5 shrink-0" />
                <span>{{ dashboard.label }}</span>
            </Link>

            <div v-for="group in groups" :key="group.label" class="mt-6">
                <p class="px-3 text-xs font-semibold text-slate-400">{{ group.label }}</p>
                <div class="mt-2 space-y-1.5">
                    <Link
                        v-for="link in group.items"
                        :key="link.routeName"
                        :href="route(link.routeName)"
                        class="admin-nav-link"
                        :class="isActive(link.routeName) ? 'admin-nav-link-active' : ''"
                        :title="link.label"
                    >
                        <component :is="link.icon" class="size-5 shrink-0" />
                        <span>{{ link.label }}</span>
                    </Link>
                </div>
            </div>
        </nav>

        <div class="border-t border-[#EEF0F6] p-4 space-y-2">
            <Link
                :href="route('shop')"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-tabarak-blue/10 p-2.5 text-xs font-bold text-tabarak-blue transition hover:bg-tabarak-blue hover:text-white"
            >
                <Store class="size-4" />
                <span>Enter website as Admin</span>
            </Link>
            <p class="text-center text-[11px] leading-4 text-slate-400">Order for customer with custom prices</p>
        </div>
    </aside>
</template>
