<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Send } from '@lucide/vue';
import { computed } from 'vue';

const form = useForm({ customer_name: '', customer_phone: '+1' });
const cartError = computed(() => (form.errors as Record<string, string>).cart);
const submit = () => form.post(route('orders.store'));
</script>

<template>
    <form class="rounded-lg border border-tabarak-line bg-white p-5 shadow-[0_12px_34px_rgba(64,88,225,0.08)]" @submit.prevent="submit">
        <p class="eyebrow">Send order</p>
        <h2 class="mt-1 font-display text-2xl font-bold text-tabarak-ink">Customer details</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">Tabarak Trading will receive this order in the admin dashboard and contact you by phone.</p>

        <label class="mt-5 block">
            <span class="field-label">Shop / owner name *</span>
            <input v-model.trim="form.customer_name" class="field-input" type="text" maxlength="180" autocomplete="organization" placeholder="Shop or owner name" required />
            <span v-if="form.errors.customer_name" class="mt-1 block text-xs font-semibold text-red-600">{{ form.errors.customer_name }}</span>
        </label>
        <label class="mt-4 block">
            <span class="field-label">U.S. phone number *</span>
            <input v-model.trim="form.customer_phone" class="field-input" type="tel" maxlength="12" inputmode="tel" autocomplete="tel" pattern="\+1[2-9][0-9]{2}[2-9][0-9]{6}" placeholder="+12125550123" required />
            <span class="mt-1.5 block text-xs text-slate-500">Use +1 followed by the 10-digit U.S. number.</span>
            <span v-if="form.errors.customer_phone" class="mt-1 block text-xs font-semibold text-red-600">{{ form.errors.customer_phone }}</span>
        </label>
        <p v-if="cartError" class="mt-3 text-sm font-semibold text-red-600">{{ cartError }}</p>

        <button class="mt-5 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-md bg-tabarak-orange px-5 text-sm font-bold text-white transition hover:bg-[#E94E00] disabled:cursor-not-allowed disabled:opacity-60" type="submit" :disabled="form.processing">
            <Send class="size-4" /> {{ form.processing ? 'Sending order…' : 'Send order to Tabarak' }}
        </button>
        <p class="mt-3 text-center text-xs text-slate-400">All amounts are charged and shown in USD.</p>
    </form>
</template>
