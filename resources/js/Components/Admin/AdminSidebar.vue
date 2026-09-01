<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Boxes, Building2, FolderTree, Gauge, Images, Settings, Sparkles } from '@lucide/vue';
import BrandMark from '@/Components/Shared/BrandMark.vue';

defineProps<{ mobile?: boolean }>();
const links = [
    { label: 'Dashboard', routeName: 'admin.dashboard', icon: Gauge },
    { label: 'Products', routeName: 'admin.products.index', icon: Boxes },
    { label: 'Categories', routeName: 'admin.categories.index', icon: FolderTree },
    { label: 'Brands', routeName: 'admin.brands.index', icon: Building2 },
    { label: 'Media Library', routeName: 'admin.media.index', icon: Images },
    { label: 'Bulk Import', routeName: 'admin.imports.index', icon: Sparkles },
    { label: 'Settings', routeName: 'admin.settings.index', icon: Settings },
];
</script>

<template>
    <aside class="flex h-full flex-col bg-forest-900 text-white" :class="mobile ? 'w-full' : 'w-[232px]'">
        <div class="border-b border-white/10 px-5 py-5"><BrandMark inverse /></div>
        <nav class="flex-1 space-y-1 p-3" aria-label="Admin navigation">
            <Link v-for="link in links" :key="link.routeName" :href="route(link.routeName)" class="flex min-h-11 items-center gap-3 rounded-md px-3 text-sm font-semibold text-forest-100 transition hover:bg-white/10 hover:text-white" :class="route().current(`${link.routeName.split('.').slice(0, 2).join('.')}*`) || route().current(link.routeName) ? 'bg-white/10 text-white' : ''">
                <component :is="link.icon" class="size-[18px]" /><span>{{ link.label }}</span>
            </Link>
        </nav>
        <div class="border-t border-white/10 p-4 text-xs leading-5 text-forest-100">Catalogue administration<br /><span class="text-white">Foundation v2</span></div>
    </aside>
</template>
