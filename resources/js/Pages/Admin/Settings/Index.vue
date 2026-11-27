<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { FileText, Save, Settings2 } from '@lucide/vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface SettingsForm {
    site_name: string;
    catalogue_intro: string;
    contact_email: string;
    company_legal_name: string;
    company_address_line_1: string;
    company_address_line_2: string;
    company_phone: string;
    company_email: string;
    invoice_policy: string;
}

const props = defineProps<{ settings: SettingsForm }>();
const form = useForm<SettingsForm>({ ...props.settings });
const submit = () => form.put(route('admin.settings.update'), { preserveScroll: true });
</script>

<template>
    <Head title="Settings" />
    <AdminLayout>
        <PageHeader eyebrow="Configuration" title="Catalogue & policy settings" description="Manage public catalogue details, company information, and the policy shown online and printed on invoices." />

        <form class="max-w-3xl space-y-6" @submit.prevent="submit">
            <section class="surface overflow-hidden">
                <div class="flex items-start gap-4 border-b border-tabarak-line bg-tabarak-mist p-5 md:p-6">
                    <span class="grid size-11 shrink-0 place-items-center rounded-lg bg-white text-tabarak-blue shadow-sm"><Settings2 class="size-5" /></span>
                    <div><h2 class="font-display text-xl font-bold text-tabarak-ink">Storefront details</h2><p class="mt-1 text-sm leading-6 text-slate-500">These values are shared with the public catalogue.</p></div>
                </div>
                <div class="space-y-5 p-5 md:p-7">
                    <label class="block"><span class="field-label">Site name</span><input v-model="form.site_name" class="field-input" required /><span v-if="form.errors.site_name" class="mt-1 block text-xs text-red-600">{{ form.errors.site_name }}</span></label>
                    <label class="block"><span class="field-label">Catalogue introduction</span><textarea v-model="form.catalogue_intro" class="field-input min-h-32" placeholder="Short wholesale catalogue introduction" /><span v-if="form.errors.catalogue_intro" class="mt-1 block text-xs text-red-600">{{ form.errors.catalogue_intro }}</span></label>
                    <label class="block"><span class="field-label">Contact email</span><input v-model="form.contact_email" class="field-input" type="email" /><span v-if="form.errors.contact_email" class="mt-1 block text-xs text-red-600">{{ form.errors.contact_email }}</span></label>
                </div>
            </section>

            <section class="surface overflow-hidden">
                <div class="flex items-start gap-4 border-b border-tabarak-line bg-tabarak-mist p-5 md:p-6">
                    <span class="grid size-11 shrink-0 place-items-center rounded-lg bg-white text-tabarak-blue shadow-sm"><FileText class="size-5" /></span>
                    <div><h2 class="font-display text-xl font-bold text-tabarak-ink">Company policy & printed invoice</h2><p class="mt-1 text-sm leading-6 text-slate-500">These company details print on invoices. The policy is also displayed publicly in the catalogue, cart, footer, and policy page.</p></div>
                </div>
                <div class="space-y-5 p-5 md:p-7">
                    <label class="block"><span class="field-label">Legal company name</span><input v-model.trim="form.company_legal_name" class="field-input" required /><span v-if="form.errors.company_legal_name" class="mt-1 block text-xs text-red-600">{{ form.errors.company_legal_name }}</span></label>
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="block"><span class="field-label">Address line 1</span><input v-model.trim="form.company_address_line_1" class="field-input" required /><span v-if="form.errors.company_address_line_1" class="mt-1 block text-xs text-red-600">{{ form.errors.company_address_line_1 }}</span></label>
                        <label class="block"><span class="field-label">Address line 2</span><input v-model.trim="form.company_address_line_2" class="field-input" required /><span v-if="form.errors.company_address_line_2" class="mt-1 block text-xs text-red-600">{{ form.errors.company_address_line_2 }}</span></label>
                    </div>
                    <label class="block"><span class="field-label">Company phone</span><input v-model.trim="form.company_phone" class="field-input" type="tel" required /><span v-if="form.errors.company_phone" class="mt-1 block text-xs text-red-600">{{ form.errors.company_phone }}</span></label>
                    <label class="block"><span class="field-label">Company email</span><input v-model.trim="form.company_email" class="field-input" type="email" required /><span v-if="form.errors.company_email" class="mt-1 block text-xs text-red-600">{{ form.errors.company_email }}</span></label>
                    <label class="block">
                        <span class="field-label">Company policy</span>
                        <textarea v-model="form.invoice_policy" class="field-input min-h-44" required />
                        <span class="mt-1.5 block text-xs leading-5 text-slate-500">Each line appears on the public Company Policy page and in the Note / Company Policy section of every invoice. Bankruptcy wording remains subject to applicable law.</span>
                        <span v-if="form.errors.invoice_policy" class="mt-1 block text-xs text-red-600">{{ form.errors.invoice_policy }}</span>
                    </label>
                </div>
            </section>

            <div class="flex justify-end"><button class="btn-primary" type="submit" :disabled="form.processing"><Save class="size-4" /> {{ form.processing ? 'Saving…' : 'Save settings' }}</button></div>
        </form>
    </AdminLayout>
</template>
