<script setup lang="ts">
import StorefrontFooter from '@/Components/Storefront/StorefrontFooter.vue';
import StorefrontHeader from '@/Components/Storefront/StorefrontHeader.vue';
</script>

<template>
    <div class="storefront-shell">
        <StorefrontHeader />
        <main><slot /></main>
        <StorefrontFooter />
    </div>
</template>
