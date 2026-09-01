<script setup lang="ts">
import { Image as ImageIcon } from '@lucide/vue';
import { computed, ref } from 'vue';
import type { ProductImage } from '@/types/catalogue';

const props = defineProps<{ images: ProductImage[]; name: string }>();
const selected = ref(props.images.find((image) => image.is_primary)?.id ?? props.images[0]?.id);
const current = computed(() => props.images.find((image) => image.id === selected.value) ?? props.images[0]);
</script>

<template>
    <div>
        <div class="surface aspect-square overflow-hidden bg-oat-100">
            <img v-if="current" :src="current.media.url" :alt="current.media.alt_text || name" class="h-full w-full object-cover" />
            <div v-else class="flex h-full flex-col items-center justify-center gap-4 text-slate-400"><ImageIcon class="size-14" /><span class="text-sm font-semibold">Product imagery coming soon</span></div>
        </div>
        <div v-if="images.length > 1" class="mt-3 grid grid-cols-5 gap-2">
            <button v-for="image in images" :key="image.id" class="aspect-square overflow-hidden border bg-white" :class="selected === image.id ? 'border-forest-800 ring-1 ring-forest-800' : 'border-oat-300'" type="button" @click="selected = image.id"><img :src="image.media.url" :alt="image.media.alt_text || name" class="h-full w-full object-cover" /></button>
        </div>
    </div>
</template>
