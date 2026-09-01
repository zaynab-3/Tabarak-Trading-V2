<script setup lang="ts">
import { ArrowLeft, ArrowRight, GripVertical, Pencil, Trash2 } from '@lucide/vue';
import type { MediaItem } from '@/types/catalogue';
import { humanFileSize } from '@/Utils/format';

const props = defineProps<{ item: MediaItem; index: number; total: number }>();
defineEmits<{
    edit: [item: MediaItem];
    remove: [item: MediaItem];
    move: [index: number, offset: number];
    dragstart: [index: number, event: DragEvent];
    drop: [index: number];
}>();

const usageCount = () => (props.item.product_images_count ?? 0)
    + (props.item.import_items_count ?? 0)
    + (props.item.category_images_count ?? 0)
    + (props.item.brand_logos_count ?? 0);
</script>

<template>
    <article
        class="surface group overflow-hidden transition hover:-translate-y-0.5 hover:shadow-soft"
        draggable="true"
        @dragstart="$emit('dragstart', index, $event)"
        @dragover.prevent
        @drop.prevent="$emit('drop', index)"
    >
        <div class="relative aspect-square bg-oat-100">
            <img :src="item.url" :alt="item.alt_text || item.original_name" class="h-full w-full object-contain" loading="lazy" />
            <span class="absolute left-2 top-2 grid size-8 cursor-grab place-items-center rounded bg-white/90 text-slate-500 shadow"><GripVertical class="size-4" /></span>
            <span class="absolute bottom-2 right-2 rounded bg-forest-950/75 px-2 py-1 text-[10px] font-bold text-white">#{{ index + 1 }}</span>
        </div>
        <div class="p-3">
            <p class="truncate text-xs font-bold text-forest-900" :title="item.original_name">{{ item.original_name }}</p>
            <p class="mt-1 truncate text-[11px] text-slate-500" :title="item.alt_text || 'No alt text'">{{ item.alt_text || 'No alt text' }}</p>
            <p class="mt-1 text-[11px] text-slate-400">{{ humanFileSize(item.size) }} · {{ usageCount() }} uses</p>
            <div class="mt-3 grid grid-cols-4 gap-1 border-t border-oat-200 pt-3">
                <button class="grid min-h-10 place-items-center rounded border border-oat-300 text-slate-600 disabled:opacity-30" type="button" aria-label="Move image earlier" :disabled="index === 0" @click="$emit('move', index, -1)"><ArrowLeft class="size-4" /></button>
                <button class="grid min-h-10 place-items-center rounded border border-oat-300 text-slate-600 disabled:opacity-30" type="button" aria-label="Move image later" :disabled="index === total - 1" @click="$emit('move', index, 1)"><ArrowRight class="size-4" /></button>
                <button class="grid min-h-10 place-items-center rounded border border-oat-300 text-forest-700" type="button" aria-label="Edit image" @click="$emit('edit', item)"><Pencil class="size-4" /></button>
                <button class="grid min-h-10 place-items-center rounded border border-red-200 text-red-600" type="button" aria-label="Delete image" @click="$emit('remove', item)"><Trash2 class="size-4" /></button>
            </div>
        </div>
    </article>
</template>
