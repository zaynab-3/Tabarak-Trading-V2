<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import StorefrontFooter from '@/Components/Storefront/StorefrontFooter.vue';
import StorefrontHeader from '@/Components/Storefront/StorefrontHeader.vue';
import FlashMessage from '@/Components/Shared/FlashMessage.vue';
import type { PageProps } from '@/types';

const page = usePage<PageProps>();
</script>

<template>
    <div class="storefront-shell">
        <StorefrontHeader />
        <div v-if="page.props.flash.success || page.props.flash.error" class="pointer-events-none fixed right-4 top-20 z-[60] w-[calc(100%-2rem)] max-w-sm [&>*]:pointer-events-auto">
            <FlashMessage :success="page.props.flash.success" :error="page.props.flash.error" />
        </div>
        <main><slot /></main>
        <StorefrontFooter />
    </div>
</template>
