<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import SelectMenu, { type SelectOption } from '@/Components/Shared/SelectMenu.vue';
import type { Category, MediaItem, TaxonomyRef } from '@/types/catalogue';

const props = defineProps<{ category?: Category | null; parents: TaxonomyRef[]; media: MediaItem[] }>();
const emit = defineEmits<{ saved: [] }>();
const form = useForm({ name: props.category?.name ?? '', parent_id: props.category?.parent_id ?? '', image_id: props.category?.image_id ?? '', description: props.category?.description ?? '', sort_order: props.category?.sort_order ?? 0, is_active: props.category?.is_active ?? true });
const parentOptions = computed<SelectOption[]>(() => [{ value: '', label: 'None' }, ...props.parents.filter((item) => item.id !== props.category?.id).map((item) => ({ value: item.id, label: item.name }))]);
const imageOptions = computed<SelectOption[]>(() => [{ value: '', label: 'No image' }, ...props.media.map((item) => ({ value: item.id, label: item.original_name || `Image #${item.id}` }))]);
const submit = () => props.category
    ? form.put(route('admin.categories.update', props.category.slug), { onSuccess: () => emit('saved') })
    : form.post(route('admin.categories.store'), { onSuccess: () => { form.reset(); emit('saved'); } });
</script>

<template>
    <form class="space-y-4" @submit.prevent="submit">
        <label class="block"><span class="field-label">Name *</span><input v-model="form.name" class="field-input" required /><span v-if="form.errors.name" class="mt-1 block text-xs text-red-600">{{ form.errors.name }}</span></label>
        <div class="grid grid-cols-2 gap-3"><label><span class="field-label">Parent</span><SelectMenu v-model="form.parent_id" :options="parentOptions" aria-label="Parent category" /></label><label><span class="field-label">Sort order</span><input v-model="form.sort_order" class="field-input" min="0" type="number" /></label></div>
        <label class="block"><span class="field-label">Category image</span><SelectMenu v-model="form.image_id" :options="imageOptions" aria-label="Category image" /></label>
        <label class="block"><span class="field-label">Description</span><textarea v-model="form.description" class="field-input min-h-28" /></label>
        <label class="flex min-h-11 items-center gap-2 text-sm font-semibold"><input v-model="form.is_active" type="checkbox" class="rounded border-tabarak-line text-tabarak-blue focus:ring-tabarak-blue" /> Active</label>
        <button class="btn-primary w-full" type="submit" :disabled="form.processing">{{ form.processing ? 'Saving…' : category ? 'Update category' : 'Create category' }}</button>
    </form>
</template>
