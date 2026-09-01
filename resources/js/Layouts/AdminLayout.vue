<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { X } from '@lucide/vue';
import { ref } from 'vue';
import AdminHeader from '@/Components/Admin/AdminHeader.vue';
import AdminSidebar from '@/Components/Admin/AdminSidebar.vue';
import FlashMessage from '@/Components/Shared/FlashMessage.vue';
import type { PageProps } from '@/types';

const mobileMenu = ref(false);
const page = usePage<PageProps>();
</script>

<template>
    <div class="min-h-screen bg-oat-50 md:grid md:grid-cols-[232px_minmax(0,1fr)]">
        <div class="fixed inset-y-0 left-0 z-50 hidden md:block"><AdminSidebar /></div>
        <div v-if="mobileMenu" class="fixed inset-0 z-50 flex md:hidden"><div class="w-[280px]"><AdminSidebar mobile /></div><button class="flex-1 bg-slate-950/45 p-4 text-left text-white" aria-label="Close navigation" @click="mobileMenu = false"><X class="size-6" /></button></div>
        <div class="min-w-0 md:col-start-2">
            <AdminHeader @menu="mobileMenu = true" />
            <main class="p-4 md:p-6 xl:p-8">
                <FlashMessage :success="page.props.flash.success" :error="page.props.flash.error" />
                <slot />
            </main>
        </div>
    </div>
</template>
