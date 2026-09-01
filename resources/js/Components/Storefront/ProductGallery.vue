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
        <div class="aspect-square overflow-hidden rounded-lg border border-tabarak-line bg-tabarak-mist p-6 md:p-8">
            <img v-if="current" :src="current.media.url" :alt="current.media.alt_text || name" class="h-full w-full object-contain" />
            <div v-else class="flex h-full flex-col items-center justify-center gap-4 text-tabarak-blue">
                <ImageIcon class="size-14" />
                <span class="text-sm font-semibold text-slate-500">Product imagery coming soon</span>
            </div>
        </div>
        <div v-if="images.length > 1" class="mt-3 grid grid-cols-5 gap-2">
            <button
                v-for="image in images"
                :key="image.id"
                class="aspect-square overflow-hidden rounded-md border bg-white p-1.5 transition"
                :class="selected === image.id ? 'border-tabarak-blue ring-1 ring-tabarak-blue' : 'border-tabarak-line hover:border-tabarak-orange'"
                type="button"
                @click="selected = image.id"
            >
                <img :src="image.media.url" :alt="image.media.alt_text || name" class="h-full w-full object-contain" />
            </button>
        </div>
    </div>
</template>
