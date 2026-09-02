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
    <div class="admin-shell min-h-screen bg-[#F8F9FC] lg:grid lg:grid-cols-[236px_minmax(0,1fr)]">
        <div class="fixed inset-y-0 left-0 z-50 hidden w-[236px] lg:block"><AdminSidebar /></div>

        <div v-if="mobileMenu" class="fixed inset-0 z-50 flex lg:hidden">
            <AdminSidebar mobile />
            <button class="flex flex-1 items-start justify-end bg-slate-950/45 p-5 text-white backdrop-blur-sm" aria-label="Close navigation" @click="mobileMenu = false">
                <X class="size-6" />
            </button>
        </div>

        <div class="min-w-0 lg:col-start-2">
            <AdminHeader @menu="mobileMenu = true" />
            <main class="mx-auto w-full max-w-[1600px] p-4 sm:p-6 lg:p-8 xl:p-10">
                <FlashMessage :success="page.props.flash.success" :error="page.props.flash.error" />
                <slot />
            </main>
        </div>
    </div>
</template>
