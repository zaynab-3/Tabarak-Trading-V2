<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { CheckCircle2, CircleAlert, Send } from '@lucide/vue';
import { computed } from 'vue';
import { canonicalUsPhoneNumber, formatUsPhoneNumber, isCompleteUsPhoneNumber, isValidUsPhoneNumber, remainingUsPhoneDigits } from '@/Utils/usPhone';

const form = useForm({ customer_name: '', customer_phone: '+1 ' });
const cartError = computed(() => (form.errors as Record<string, string>).cart);
const phoneComplete = computed(() => isCompleteUsPhoneNumber(form.customer_phone));
const phoneValid = computed(() => isValidUsPhoneNumber(form.customer_phone));
const remainingDigits = computed(() => remainingUsPhoneDigits(form.customer_phone));

const formatPhone = (event: Event) => {
    form.customer_phone = formatUsPhoneNumber((event.target as HTMLInputElement).value);
};

const submit = () => {
    if (!phoneValid.value) return;

    form.transform((data) => ({
        ...data,
        customer_phone: canonicalUsPhoneNumber(data.customer_phone),
    })).post(route('orders.store'));
};
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
            <input :value="form.customer_phone" class="field-input" type="tel" maxlength="17" inputmode="tel" autocomplete="tel" placeholder="+1 (202) 222 2222" required @input="formatPhone" />
            <span v-if="!phoneComplete" class="mt-1.5 block text-xs text-slate-500" aria-live="polite">Enter {{ remainingDigits }} more digit{{ remainingDigits === 1 ? '' : 's' }}. It will format automatically.</span>
            <span v-else-if="phoneValid" class="mt-1.5 flex items-center gap-1.5 text-xs font-bold text-emerald-700" aria-live="polite"><CheckCircle2 class="size-4" /> Valid U.S. phone number format</span>
            <span v-else class="mt-1.5 flex items-center gap-1.5 text-xs font-bold text-red-600" aria-live="polite"><CircleAlert class="size-4" /> This U.S. phone number format is not valid</span>
            <span v-if="form.errors.customer_phone" class="mt-1 block text-xs font-semibold text-red-600">{{ form.errors.customer_phone }}</span>
        </label>
        <p v-if="cartError" class="mt-3 text-sm font-semibold text-red-600">{{ cartError }}</p>

        <button class="mt-5 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-md bg-tabarak-orange px-5 text-sm font-bold text-white transition hover:bg-[#E94E00] disabled:cursor-not-allowed disabled:opacity-60" type="submit" :disabled="form.processing || !phoneValid">
            <Send class="size-4" /> {{ form.processing ? 'Sending order…' : 'Send order to Tabarak' }}
        </button>
        <p class="mt-3 text-center text-xs text-slate-400">All amounts are charged and shown in USD.</p>
    </form>
</template>
