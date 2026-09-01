<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import MediaEditModal from '@/Components/Admin/MediaEditModal.vue';
import MediaLibraryCard from '@/Components/Admin/MediaLibraryCard.vue';
import MediaUploader from '@/Components/Admin/MediaUploader.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import Pagination from '@/Components/Shared/Pagination.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { MediaItem, Paginated } from '@/types/catalogue';

const props = defineProps<{ media: Paginated<MediaItem> }>();
const form = useForm<{ images: File[]; alt_text: string }>({ images: [], alt_text: '' });
const items = ref<MediaItem[]>([...props.media.data]);
const editing = ref<MediaItem | null>(null);
const draggedIndex = ref<number | null>(null);

watch(() => props.media.data, (value) => { items.value = [...value]; });

const selected = (files: File[]) => { form.images = files; };
const upload = () => form.post(route('admin.media.store'), { forceFormData: true, onSuccess: () => form.reset() });

const usageCount = (item: MediaItem) => (item.product_images_count ?? 0)
    + (item.import_items_count ?? 0)
    + (item.category_images_count ?? 0)
    + (item.brand_logos_count ?? 0);

const remove = (item: MediaItem) => {
    const uses = usageCount(item);
    const warning = uses
        ? `This image is used in ${uses} place(s). Deleting it will remove those catalogue links. Continue?`
        : 'Permanently delete this image?';
    if (window.confirm(warning)) router.delete(route('admin.media.destroy', item.id), { preserveScroll: true });
};

const saveOrder = () => router.put(
    route('admin.media.order.update'),
    { media_ids: items.value.map((item) => item.id) },
    { preserveScroll: true, preserveState: true },
);

const move = (index: number, offset: number) => {
    const target = index + offset;
    if (target < 0 || target >= items.value.length) return;
    const [item] = items.value.splice(index, 1);
    items.value.splice(target, 0, item);
    saveOrder();
};

const startDrag = (index: number, event: DragEvent) => {
    draggedIndex.value = index;
    if (event.dataTransfer) event.dataTransfer.effectAllowed = 'move';
};

const drop = (target: number) => {
    if (draggedIndex.value === null || draggedIndex.value === target) return;
    const [item] = items.value.splice(draggedIndex.value, 1);
    items.value.splice(target, 0, item);
    draggedIndex.value = null;
    saveOrder();
};
</script>

<template>
    <Head title="Media library" /><AdminLayout><PageHeader eyebrow="Assets" title="Media library" :description="`${media.total} safely named catalogue images with usage metadata.`" />
        <form class="surface mb-6 grid gap-4 p-5 md:grid-cols-[1fr_260px] md:items-end" @submit.prevent="upload"><MediaUploader multiple @change="selected" /><div><label class="block"><span class="field-label">Shared alt text</span><input v-model="form.alt_text" class="field-input" placeholder="Optional descriptive text" /></label><button class="btn-primary mt-3 w-full" type="submit" :disabled="!form.images.length || form.processing">{{ form.processing ? 'Uploading…' : 'Upload to library' }}</button></div></form>
        <p v-if="items.length" class="mb-3 text-xs text-slate-500">Drag images to reorder them, or use the arrow buttons on tablet. Changes save automatically.</p>
        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-6">
            <MediaLibraryCard v-for="(item, index) in items" :key="item.id" :item="item" :index="index" :total="items.length" @edit="editing = $event" @remove="remove" @move="move" @dragstart="startDrag" @drop="drop" />
        </div>
        <div class="mt-8"><Pagination :links="media.links" /></div>
        <MediaEditModal v-if="editing" :key="editing.id" :item="editing" @close="editing = null" />
    </AdminLayout>
</template>
