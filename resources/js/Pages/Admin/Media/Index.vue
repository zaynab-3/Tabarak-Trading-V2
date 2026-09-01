<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Trash2 } from '@lucide/vue';
import MediaUploader from '@/Components/Admin/MediaUploader.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import Pagination from '@/Components/Shared/Pagination.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { MediaItem, Paginated } from '@/types/catalogue';
import { humanFileSize } from '@/Utils/format';

defineProps<{ media: Paginated<MediaItem> }>();
const form = useForm<{ images: File[]; alt_text: string }>({ images: [], alt_text: '' });
const selected = (files: File[]) => { form.images = files; };
const upload = () => form.post(route('admin.media.store'), { forceFormData: true, onSuccess: () => form.reset() });
const remove = (item: MediaItem) => { if (window.confirm('Permanently delete this unused image?')) router.delete(route('admin.media.destroy', item.id)); };
</script>

<template>
    <Head title="Media library" /><AdminLayout><PageHeader eyebrow="Assets" title="Media library" :description="`${media.total} safely named catalogue images with usage metadata.`" />
        <form class="surface mb-6 grid gap-4 p-5 md:grid-cols-[1fr_260px] md:items-end" @submit.prevent="upload"><MediaUploader multiple @change="selected" /><div><label class="block"><span class="field-label">Shared alt text</span><input v-model="form.alt_text" class="field-input" placeholder="Optional descriptive text" /></label><button class="btn-primary mt-3 w-full" type="submit" :disabled="!form.images.length || form.processing">{{ form.processing ? 'Uploading…' : 'Upload to library' }}</button></div></form>
        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6"><article v-for="item in media.data" :key="item.id" class="surface overflow-hidden"><div class="aspect-square bg-oat-100"><img :src="item.url" :alt="item.alt_text || item.original_name" class="h-full w-full object-cover" loading="lazy" /></div><div class="p-3"><p class="truncate text-xs font-bold text-forest-900" :title="item.original_name">{{ item.original_name }}</p><p class="mt-1 text-[11px] text-slate-400">{{ humanFileSize(item.size) }} · {{ (item.product_images_count || 0) + (item.import_items_count || 0) }} uses</p><button v-if="!(item.product_images_count || item.import_items_count)" class="mt-3 inline-flex items-center gap-1 text-xs font-bold text-red-600" type="button" @click="remove(item)"><Trash2 class="size-3.5" /> Delete</button></div></article></div><div class="mt-8"><Pagination :links="media.links" /></div>
    </AdminLayout>
</template>
