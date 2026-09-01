<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Star, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import ConfirmDialog from '@/Components/Shared/ConfirmDialog.vue';
import type { Product, ProductImage } from '@/types/catalogue';
import MediaUploader from '@/Components/Admin/MediaUploader.vue';

const props = defineProps<{ product: Product }>();
const form = useForm<{ images: File[] }>({ images: [] });
const pendingDelete = ref<ProductImage | null>(null);
const deleteProcessing = ref(false);
const selected = (files: File[]) => { form.images = files; };
const upload = () => form.post(route('admin.products.images.store', props.product.slug), { forceFormData: true, onSuccess: () => form.reset() });
const remove = (image: ProductImage) => { pendingDelete.value = image; };
const confirmDelete = () => {
    if (!pendingDelete.value) return;
    deleteProcessing.value = true;
    router.delete(route('admin.products.images.destroy', [props.product.slug, pendingDelete.value.id]), {
        preserveScroll: true,
        onFinish: () => { deleteProcessing.value = false; pendingDelete.value = null; },
    });
};
const primary = (image: ProductImage) => router.patch(route('admin.products.images.primary', [props.product.slug, image.id]));
</script>

<template>
    <section class="mt-6 surface p-5 md:p-6">
        <div class="mb-5 border-b border-tabarak-line pb-4"><p class="eyebrow">Gallery</p><h2 class="mt-1 font-display text-2xl font-bold text-tabarak-ink">Product images</h2><p class="mt-1 text-sm text-slate-500">Upload multiple views, choose one primary image, and keep ordering predictable.</p></div>
        <div class="grid gap-5 lg:grid-cols-[320px_1fr]">
            <form class="space-y-3" @submit.prevent="upload"><MediaUploader multiple @change="selected" /><p v-if="form.errors.images" class="text-xs text-red-600">{{ form.errors.images }}</p><button class="btn-primary w-full" type="submit" :disabled="!form.images.length || form.processing">{{ form.processing ? 'Uploading…' : 'Upload selected images' }}</button></form>
            <div v-if="product.images?.length" class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
                <article v-for="image in product.images" :key="image.id" class="overflow-hidden rounded-lg border border-tabarak-line bg-white"><div class="aspect-square bg-tabarak-mist p-2"><img :src="image.media.url" :alt="image.media.alt_text || product.name" class="h-full w-full object-contain" /></div><div class="flex items-center justify-between gap-1 p-2"><button class="admin-table-action" :class="image.is_primary ? 'border-tabarak-orange bg-[#FFF0E8] text-tabarak-orange' : ''" type="button" :aria-label="image.is_primary ? 'Primary image' : 'Set as primary image'" @click="primary(image)"><Star class="size-4" :fill="image.is_primary ? 'currentColor' : 'none'" /></button><button class="admin-table-action border-red-200 text-red-600" type="button" aria-label="Remove image" @click="remove(image)"><Trash2 class="size-4" /></button></div></article>
            </div>
            <div v-else class="flex min-h-48 items-center justify-center rounded-lg border border-dashed border-tabarak-line bg-tabarak-mist text-sm text-slate-500">No product images yet.</div>
        </div>
        <ConfirmDialog
            :open="Boolean(pendingDelete)"
            title="Remove product image?"
            description="This image will be detached from this product. The media-library file will remain available."
            confirm-label="Remove image"
            :processing="deleteProcessing"
            @cancel="pendingDelete = null"
            @confirm="confirmDelete"
        />
    </section>
</template>
