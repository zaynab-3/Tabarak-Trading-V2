<script setup lang="ts">
import { LoaderCircle, UploadCloud } from '@lucide/vue';
import { ref } from 'vue';
import { isSvgFile, svgToPng } from '@/Utils/svgToPng';

defineProps<{ multiple?: boolean; accept?: string; help?: string }>();
const emit = defineEmits<{ change: [files: File[]] }>();
const selectedCount = ref(0);
const preparing = ref(false);
const error = ref<string | null>(null);

const changed = async (event: Event) => {
    const files = Array.from((event.target as HTMLInputElement).files ?? []);
    selectedCount.value = files.length;
    error.value = null;
    preparing.value = files.some(isSvgFile);
    emit('change', []);

    try {
        const prepared = await Promise.all(files.map((file) => isSvgFile(file) ? svgToPng(file) : file));
        emit('change', prepared);
    } catch (caught) {
        selectedCount.value = 0;
        (event.target as HTMLInputElement).value = '';
        emit('change', []);
        error.value = caught instanceof Error ? caught.message : 'The SVG image could not be prepared.';
    } finally {
        preparing.value = false;
    }
};
</script>

<template>
    <label class="flex min-h-44 cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-tabarak-line bg-tabarak-mist px-6 py-8 text-center transition hover:border-tabarak-blue hover:bg-white">
        <span class="grid size-12 place-items-center rounded-lg bg-white text-tabarak-blue shadow-sm"><LoaderCircle v-if="preparing" class="size-6 animate-spin" /><UploadCloud v-else class="size-6" /></span><span class="mt-3 text-sm font-bold text-tabarak-ink">{{ preparing ? 'Preparing SVG…' : 'Choose product images' }}</span><span class="mt-1 text-xs text-slate-500">{{ help || 'JPG, PNG, WebP or SVG · up to 8 MB each · automatically optimized' }}</span>
        <span v-if="selectedCount && !preparing" class="mt-3 rounded-full bg-[#FFF0E8] px-3 py-1 text-xs font-semibold text-tabarak-orange">{{ selectedCount }} file{{ selectedCount === 1 ? '' : 's' }} selected</span>
        <span v-if="error" class="mt-3 text-xs font-semibold text-red-600" role="alert">{{ error }}</span>
        <input class="sr-only" type="file" :multiple="multiple" :accept="accept || 'image/jpeg,image/png,image/webp,image/svg+xml,.svg'" @change="changed" />
    </label>
</template>
