<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { LockKeyhole, Mail } from '@lucide/vue';
import AdminAuthLayout from '@/Layouts/AdminAuthLayout.vue';

const form = useForm({ email: '', password: '', remember: false });
const submit = () => form.post(route('admin.login.store'), { onFinish: () => form.reset('password') });
</script>

<template>
    <Head title="Admin sign in" />
    <AdminAuthLayout>
        <div class="surface p-6 md:p-8"><p class="eyebrow">Protected area</p><h2 class="mt-2 font-display text-3xl font-bold text-forest-900">Admin sign in</h2><p class="mt-2 text-sm leading-6 text-slate-500">Use your Tabarak administrator credentials to continue.</p>
            <form class="mt-7 space-y-5" @submit.prevent="submit">
                <label class="block"><span class="field-label">Email address</span><span class="relative block"><Mail class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" /><input v-model="form.email" class="field-input pl-10" type="email" autocomplete="username" required autofocus /></span><span v-if="form.errors.email" class="mt-1.5 block text-xs text-red-600">{{ form.errors.email }}</span></label>
                <label class="block"><span class="field-label">Password</span><span class="relative block"><LockKeyhole class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" /><input v-model="form.password" class="field-input pl-10" type="password" autocomplete="current-password" required /></span></label>
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-600"><input v-model="form.remember" type="checkbox" class="rounded border-oat-300 text-forest-800 focus:ring-forest-700" /> Keep me signed in</label>
                <button class="btn-primary w-full" type="submit" :disabled="form.processing">{{ form.processing ? 'Signing in…' : 'Sign in to admin' }}</button>
            </form>
        </div>
    </AdminAuthLayout>
</template>
