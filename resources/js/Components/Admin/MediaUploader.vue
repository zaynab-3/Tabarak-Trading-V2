<script setup lang="ts">
import { UploadCloud } from '@lucide/vue';
import { ref } from 'vue';

defineProps<{ multiple?: boolean; accept?: string; help?: string }>();
const emit = defineEmits<{ change: [files: File[]] }>();
const names = ref<string[]>([]);

const changed = (event: Event) => {
    const files = Array.from((event.target as HTMLInputElement).files ?? []);
    names.value = files.map((file) => file.name);
    emit('change', files);
};
</script>

<template>
    <label class="flex min-h-44 cursor-pointer flex-col items-center justify-center rounded-md border-2 border-dashed border-oat-300 bg-oat-50 px-6 py-8 text-center transition hover:border-forest-600 hover:bg-forest-50/40">
        <UploadCloud class="size-8 text-forest-700" /><span class="mt-3 text-sm font-bold text-forest-900">Choose product images</span><span class="mt-1 text-xs text-slate-500">{{ help || 'JPG, PNG or WebP · up to 8 MB each' }}</span>
        <span v-if="names.length" class="mt-3 text-xs font-semibold text-saffron-600">{{ names.length }} file{{ names.length === 1 ? '' : 's' }} selected</span>
        <input class="sr-only" type="file" :multiple="multiple" :accept="accept || 'image/jpeg,image/png,image/webp'" @change="changed" />
    </label>
</template>
