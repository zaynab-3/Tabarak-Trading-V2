<script setup lang="ts">
import { UploadCloud } from '@lucide/vue';
import { ref } from 'vue';

defineProps<{ multiple?: boolean; accept?: string; help?: string }>();
const emit = defineEmits<{ change: [files: File[]] }>();
const selectedCount = ref(0);

const changed = (event: Event) => {
    const files = Array.from((event.target as HTMLInputElement).files ?? []);
    selectedCount.value = files.length;
    emit('change', files);
};
</script>

<template>
    <label class="flex min-h-44 cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-tabarak-line bg-tabarak-mist px-6 py-8 text-center transition hover:border-tabarak-blue hover:bg-white">
        <span class="grid size-12 place-items-center rounded-lg bg-white text-tabarak-blue shadow-sm"><UploadCloud class="size-6" /></span><span class="mt-3 text-sm font-bold text-tabarak-ink">Choose product images</span><span class="mt-1 text-xs text-slate-500">{{ help || 'JPG, PNG or WebP · up to 8 MB each' }}</span>
        <span v-if="selectedCount" class="mt-3 rounded-full bg-[#FFF0E8] px-3 py-1 text-xs font-semibold text-tabarak-orange">{{ selectedCount }} file{{ selectedCount === 1 ? '' : 's' }} selected</span>
        <input class="sr-only" type="file" :multiple="multiple" :accept="accept || 'image/jpeg,image/png,image/webp'" @change="changed" />
    </label>
</template>
