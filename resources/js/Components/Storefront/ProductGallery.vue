<script setup lang="ts">
import { Image as ImageIcon } from '@lucide/vue';
import { computed, ref } from 'vue';
import type { ProductImage } from '@/types/catalogue';

const props = defineProps<{ images: ProductImage[]; name: string }>();
const selected = ref(props.images.find((image) => image.is_primary)?.id ?? props.images[0]?.id);
const current = computed(() => props.images.find((image) => image.id === selected.value) ?? props.images[0]);
</script>

<template>
    <div class="min-w-0">
        <div class="grid aspect-square w-full place-items-center overflow-hidden rounded-[26px] bg-[#F0EFEA] p-5 sm:p-8 lg:rounded-[32px] lg:p-10">
            <img
                v-if="current"
                :src="current.media.url"
                :alt="current.media.alt_text || name"
                class="max-h-[92%] max-w-[92%] object-contain object-center"
            />
            <div v-else class="flex h-full flex-col items-center justify-center gap-4 text-slate-400">
                <ImageIcon class="size-12" />
                <span class="text-sm font-bold">Product imagery coming soon</span>
            </div>
        </div>

        <div v-if="images.length > 1" class="no-scrollbar mt-3 flex gap-2 overflow-x-auto pb-1">
            <button
                v-for="image in images"
                :key="image.id"
                class="grid size-16 shrink-0 place-items-center overflow-hidden rounded-2xl bg-[#F0EFEA] p-1.5 transition sm:size-20"
                :class="selected === image.id ? 'ring-2 ring-tabarak-orange ring-offset-2 ring-offset-[#F7F6F2]' : 'opacity-65 hover:opacity-100'"
                type="button"
                @click="selected = image.id"
            >
                <img :src="image.media.url" :alt="image.media.alt_text || name" class="max-h-full max-w-full object-contain" />
            </button>
        </div>
    </div>
</template>
