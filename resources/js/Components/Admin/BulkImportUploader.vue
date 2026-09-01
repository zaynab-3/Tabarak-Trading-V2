<script setup lang="ts">
import { LoaderCircle, Sparkles } from '@lucide/vue';
import MediaUploader from '@/Components/Admin/MediaUploader.vue';
import { useBulkImageImport } from '@/Composables/useBulkImageImport';
import type { ImportConfiguration } from '@/types/catalogue';

const props = defineProps<{ config: ImportConfiguration }>();
const {
    batchUrl,
    error,
    files,
    name,
    processing,
    progress,
    setFiles,
    submit,
    uploaded,
} = useBulkImageImport(props.config.upload_chunk_size, props.config.max_image_size_mb);
</script>

<template>
    <form class="surface p-5" @submit.prevent="submit">
        <label class="mb-4 block">
            <span class="field-label">Batch name</span>
            <input v-model="name" class="field-input" placeholder="September nut range" :disabled="processing" />
        </label>

        <MediaUploader
            multiple
            :help="`JPG, PNG or WebP · ${config.max_image_size_mb} MB each · large selections upload in groups of ${config.upload_chunk_size}`"
            @change="setFiles"
        />

        <div v-if="processing" class="mt-4">
            <div class="mb-2 flex items-center justify-between text-xs font-bold text-forest-800">
                <span>Uploading {{ uploaded }} of {{ files.length }}</span>
                <span>{{ progress }}%</span>
            </div>
            <div class="h-2 overflow-hidden rounded-full bg-oat-200">
                <div class="h-full rounded-full bg-saffron-500 transition-all" :style="{ width: `${progress}%` }" />
            </div>
            <p class="mt-2 text-xs text-slate-500">AI analysis starts in the queue while the remaining image groups upload.</p>
        </div>

        <p v-if="error" class="mt-3 border-l-2 border-red-500 pl-3 text-sm text-red-700">
            {{ error }}
            <a v-if="batchUrl" :href="batchUrl" class="ml-1 font-bold underline">Open the partially uploaded batch</a>
        </p>

        <button class="btn-primary mt-4 w-full" type="submit" :disabled="!files.length || processing">
            <LoaderCircle v-if="processing" class="size-4 animate-spin" />
            <Sparkles v-else class="size-4" />
            {{ processing ? 'Uploading and queueing analysis…' : `Upload and analyze ${files.length || 0} images` }}
        </button>
    </form>
</template>
