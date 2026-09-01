<script setup lang="ts">
defineProps<{ label: string }>();
</script>

<template>
    <div class="surface overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] border-collapse text-left text-sm" :aria-label="label">
                <slot />
            </table>
        </div>
    </div>
</template>
