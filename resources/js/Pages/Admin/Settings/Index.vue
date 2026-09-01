<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Save, Settings2 } from '@lucide/vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps<{ settings: { site_name: string; catalogue_intro: string; contact_email: string } }>();
const form = useForm({ ...props.settings });
const submit = () => form.put(route('admin.settings.update'));
</script>

<template>
    <Head title="Settings" />
    <AdminLayout>
        <PageHeader eyebrow="Configuration" title="Catalogue settings" description="Manage the public catalogue identity and contact information." />
        <form class="surface max-w-3xl overflow-hidden" @submit.prevent="submit">
            <div class="flex items-start gap-4 border-b border-tabarak-line bg-tabarak-mist p-5 md:p-6">
                <span class="grid size-11 shrink-0 place-items-center rounded-lg bg-white text-tabarak-blue shadow-sm"><Settings2 class="size-5" /></span>
                <div><h2 class="font-display text-xl font-bold text-tabarak-ink">Storefront details</h2><p class="mt-1 text-sm leading-6 text-slate-500">These values are shared with the public catalogue.</p></div>
            </div>
            <div class="space-y-5 p-5 md:p-7">
                <label class="block"><span class="field-label">Site name</span><input v-model="form.site_name" class="field-input" required /><span v-if="form.errors.site_name" class="mt-1 block text-xs text-red-600">{{ form.errors.site_name }}</span></label>
                <label class="block"><span class="field-label">Catalogue introduction</span><textarea v-model="form.catalogue_intro" class="field-input min-h-32" placeholder="Short wholesale catalogue introduction" /></label>
                <label class="block"><span class="field-label">Contact email</span><input v-model="form.contact_email" class="field-input" type="email" /></label>
                <div class="flex justify-end border-t border-tabarak-line pt-5"><button class="btn-primary" type="submit" :disabled="form.processing"><Save class="size-4" /> {{ form.processing ? 'Saving…' : 'Save settings' }}</button></div>
            </div>
        </form>
    </AdminLayout>
</template>
