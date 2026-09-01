<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Save, X } from '@lucide/vue';
import type { MediaItem } from '@/types/catalogue';

const props = defineProps<{ item: MediaItem }>();
const emit = defineEmits<{ close: [] }>();
const form = useForm({
    original_name: props.item.original_name ?? '',
    alt_text: props.item.alt_text ?? '',
});

const submit = () => form.put(route('admin.media.update', props.item.id), {
    preserveScroll: true,
    onSuccess: () => emit('close'),
});
</script>

<template>
    <div class="fixed inset-0 z-50 grid place-items-center bg-slate-950/60 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-label="Edit image details" @click.self="$emit('close')">
        <form class="w-full max-w-xl overflow-hidden rounded-lg bg-white shadow-2xl" @submit.prevent="submit">
            <div class="flex items-center justify-between border-b border-tabarak-line px-5 py-4">
                <div><p class="eyebrow">Media details</p><h2 class="mt-1 font-display text-2xl font-bold text-tabarak-ink">Edit image</h2></div>
                <button class="admin-icon-button" type="button" aria-label="Close" @click="$emit('close')"><X class="size-5" /></button>
            </div>
            <div class="grid gap-5 p-5 md:grid-cols-[180px_1fr]">
                <div class="aspect-square overflow-hidden rounded-lg bg-tabarak-mist p-2"><img :src="item.url" :alt="form.alt_text || form.original_name" class="h-full w-full object-contain" /></div>
                <div class="space-y-4">
                    <label class="block"><span class="field-label">Image name</span><input v-model="form.original_name" class="field-input" maxlength="255" required /><span v-if="form.errors.original_name" class="mt-1 block text-xs text-red-600">{{ form.errors.original_name }}</span></label>
                    <label class="block"><span class="field-label">Alt text</span><textarea v-model="form.alt_text" class="field-input min-h-28" maxlength="255" placeholder="Describe the image for accessibility and search." /><span v-if="form.errors.alt_text" class="mt-1 block text-xs text-red-600">{{ form.errors.alt_text }}</span></label>
                </div>
            </div>
            <div class="flex justify-end gap-2 border-t border-tabarak-line bg-tabarak-mist px-5 py-4">
                <button class="btn-secondary" type="button" @click="$emit('close')">Cancel</button>
                <button class="btn-primary" type="submit" :disabled="form.processing"><Save class="size-4" /> {{ form.processing ? 'Saving…' : 'Save changes' }}</button>
            </div>
        </form>
    </div>
</template>
