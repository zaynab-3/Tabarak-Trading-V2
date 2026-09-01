<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import type { Brand, MediaItem } from '@/types/catalogue';

const props = defineProps<{ brand?: Brand | null; media: MediaItem[] }>();
const emit = defineEmits<{ saved: [] }>();
const form = useForm({ name: props.brand?.name ?? '', logo_image_id: props.brand?.logo_image_id ?? '', description: props.brand?.description ?? '', is_active: props.brand?.is_active ?? true });
const submit = () => props.brand
    ? form.put(route('admin.brands.update', props.brand.slug), { onSuccess: () => emit('saved') })
    : form.post(route('admin.brands.store'), { onSuccess: () => { form.reset(); emit('saved'); } });
</script>

<template>
    <form class="space-y-4" @submit.prevent="submit">
        <label class="block"><span class="field-label">Brand name *</span><input v-model="form.name" class="field-input" required /><span v-if="form.errors.name" class="mt-1 block text-xs text-red-600">{{ form.errors.name }}</span></label>
        <label class="block"><span class="field-label">Logo image</span><select v-model="form.logo_image_id" class="field-input"><option value="">No logo</option><option v-for="item in media" :key="item.id" :value="item.id">{{ item.original_name || `Image #${item.id}` }}</option></select></label>
        <label class="block"><span class="field-label">Description</span><textarea v-model="form.description" class="field-input min-h-28" /></label>
        <label class="flex min-h-11 items-center gap-2 text-sm font-semibold"><input v-model="form.is_active" type="checkbox" class="rounded border-tabarak-line text-tabarak-blue focus:ring-tabarak-blue" /> Active</label>
        <button class="btn-primary w-full" type="submit" :disabled="form.processing">{{ form.processing ? 'Saving…' : brand ? 'Update brand' : 'Create brand' }}</button>
    </form>
</template>
