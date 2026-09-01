<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Boxes, Building2, ClipboardList, FolderTree, Gauge, Images, Settings, Sparkles } from '@lucide/vue';
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
        items: [{ label: 'Orders', routeName: 'admin.orders.index', icon: ClipboardList }],
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
    <aside
        class="flex h-full flex-col bg-tabarak-blue text-white shadow-[10px_0_36px_rgba(64,88,225,0.12)]"
        :class="mobile ? 'w-[288px]' : 'w-[84px] xl:w-[248px]'"
    >
        <div class="flex min-h-[76px] items-center border-b border-white/15 px-4 xl:px-5">
            <BrandMark v-if="mobile" inverse />
            <template v-else>
                <BrandMark compact inverse class="xl:hidden" />
                <BrandMark inverse class="hidden xl:inline-flex" />
            </template>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-5" aria-label="Admin navigation">
            <Link
                :href="route(dashboard.routeName)"
                class="admin-nav-link"
                :class="[isActive(dashboard.routeName) ? 'admin-nav-link-active' : '', mobile ? 'justify-start' : 'justify-center xl:justify-start']"
                :title="dashboard.label"
            >
                <component :is="dashboard.icon" class="size-5 shrink-0" />
                <span :class="mobile ? 'block' : 'hidden xl:block'">{{ dashboard.label }}</span>
            </Link>

            <div v-for="group in groups" :key="group.label" class="mt-6">
                <p class="px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-white/55" :class="mobile ? 'block' : 'hidden xl:block'">{{ group.label }}</p>
                <div class="mt-2 space-y-1.5">
                    <Link
                        v-for="link in group.items"
                        :key="link.routeName"
                        :href="route(link.routeName)"
                        class="admin-nav-link"
                        :class="[isActive(link.routeName) ? 'admin-nav-link-active' : '', mobile ? 'justify-start' : 'justify-center xl:justify-start']"
                        :title="link.label"
                    >
                        <component :is="link.icon" class="size-5 shrink-0" />
                        <span :class="mobile ? 'block' : 'hidden xl:block'">{{ link.label }}</span>
                    </Link>
                </div>
            </div>
        </nav>

        <div class="border-t border-white/15 p-4">
            <p class="text-center text-[10px] font-bold uppercase tracking-[0.16em] text-white/55" :class="mobile ? 'text-left' : 'xl:text-left'">
                <span :class="mobile ? 'inline' : 'xl:hidden'">V2</span>
                <span :class="mobile ? 'hidden' : 'hidden xl:inline'">Tabarak administration</span>
            </p>
        </div>
    </aside>
</template>
