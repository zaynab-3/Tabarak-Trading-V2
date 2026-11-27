<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { CheckCircle2, CircleAlert, Send, ShieldCheck, X } from '@lucide/vue';
import { computed, onBeforeUnmount, watch } from 'vue';
import { canonicalUsPhoneNumber, formatUsPhoneNumber, isCompleteUsPhoneNumber, isValidUsPhoneNumber, remainingUsPhoneDigits } from '@/Utils/usPhone';
import { formatMoney } from '@/Utils/format';

const props = defineProps<{ open: boolean; total?: string; itemCount?: number }>();
const emit = defineEmits<{ close: [] }>();

const form = useForm({
    customer_name: '',
    customer_phone: '+1 ',
    billing_address: '',
    shipping_address: '',
});
const cartError = computed(() => (form.errors as Record<string, string>).cart);
const phoneComplete = computed(() => isCompleteUsPhoneNumber(form.customer_phone));
const phoneValid = computed(() => isValidUsPhoneNumber(form.customer_phone));
const remainingDigits = computed(() => remainingUsPhoneDigits(form.customer_phone));

const formatPhone = (event: Event) => {
    form.customer_phone = formatUsPhoneNumber((event.target as HTMLInputElement).value);
};

const submit = () => {
    if (!phoneValid.value || !form.billing_address.trim()) return;
    form.transform((data) => ({ ...data, customer_phone: canonicalUsPhoneNumber(data.customer_phone) })).post(route('orders.store'));
};

const close = () => { if (!form.processing) emit('close'); };
const onKeydown = (event: KeyboardEvent) => { if (event.key === 'Escape') close(); };

watch(() => props.open, (isOpen) => {
    document.body.style.overflow = isOpen ? 'hidden' : '';
    if (isOpen) window.addEventListener('keydown', onKeydown);
    else window.removeEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => {
    document.body.style.overflow = '';
    window.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <Teleport to="body">
        <Transition enter-active-class="transition-opacity duration-200 ease-out" enter-from-class="opacity-0" leave-active-class="transition-opacity duration-150 ease-in" leave-to-class="opacity-0">
            <div v-if="open" class="fixed inset-0 z-[90] bg-[#17191D]/42 backdrop-blur-[5px]" aria-hidden="true" @click="close"></div>
        </Transition>

        <Transition enter-active-class="transition duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]" enter-from-class="translate-y-8 opacity-0" leave-active-class="transition duration-180 ease-in" leave-to-class="translate-y-6 opacity-0">
            <section
                v-if="open"
                class="fixed inset-x-3 bottom-3 z-[91] mx-auto flex max-h-[90dvh] w-auto max-w-2xl flex-col overflow-hidden rounded-[30px] bg-white shadow-[0_28px_90px_rgba(25,27,32,0.24)] sm:inset-x-6 sm:bottom-6"
                role="dialog"
                aria-modal="true"
                aria-labelledby="checkout-title"
            >
                <div class="relative shrink-0 px-5 pb-3 pt-3 sm:px-7">
                    <span class="mx-auto block h-1 w-10 rounded-full bg-[#D8D5CF]" aria-hidden="true"></span>
                    <button type="button" class="absolute right-4 top-4 grid size-10 place-items-center rounded-full text-slate-500 transition hover:bg-[#F2F0EB] hover:text-tabarak-ink" aria-label="Close checkout" @click="close"><X class="size-5" /></button>
                    <p class="mt-4 eyebrow">Almost done</p>
                    <h2 id="checkout-title" class="mt-1 font-display text-3xl font-bold tracking-[-0.04em] text-tabarak-ink">Checkout details</h2>
                    <p v-if="total" class="mt-1.5 text-sm font-semibold text-slate-400">{{ itemCount }} item{{ itemCount === 1 ? '' : 's' }} · {{ formatMoney(total) }} USD</p>
                </div>

                <form class="flex min-h-0 flex-1 flex-col" @submit.prevent="submit">
                    <div class="no-scrollbar min-h-0 flex-1 overflow-y-auto px-5 pb-5 pt-2 sm:px-7">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span class="field-label">Shop / owner name *</span>
                                <input v-model.trim="form.customer_name" class="field-input" type="text" maxlength="180" autocomplete="organization" placeholder="Shop or owner name" required />
                                <span v-if="form.errors.customer_name" class="mt-1 block text-xs font-bold text-red-600">{{ form.errors.customer_name }}</span>
                            </label>

                            <label class="block">
                                <span class="field-label">U.S. phone number *</span>
                                <input :value="form.customer_phone" class="field-input" type="tel" maxlength="17" inputmode="tel" autocomplete="tel" placeholder="+1 (202) 222 2222" required @input="formatPhone" />
                                <span v-if="!phoneComplete" class="mt-1 block text-[11px] font-semibold text-slate-400" aria-live="polite">{{ remainingDigits }} more digit{{ remainingDigits === 1 ? '' : 's' }}</span>
                                <span v-else-if="phoneValid" class="mt-1 flex items-center gap-1 text-[11px] font-bold text-emerald-700" aria-live="polite"><CheckCircle2 class="size-3.5" /> Valid number</span>
                                <span v-else class="mt-1 flex items-center gap-1 text-[11px] font-bold text-red-600" aria-live="polite"><CircleAlert class="size-3.5" /> Invalid U.S. number</span>
                                <span v-if="form.errors.customer_phone" class="mt-1 block text-xs font-bold text-red-600">{{ form.errors.customer_phone }}</span>
                            </label>

                            <label class="block">
                                <span class="field-label">Billing address *</span>
                                <textarea v-model.trim="form.billing_address" class="field-input min-h-24 resize-none" maxlength="600" autocomplete="street-address" placeholder="Street address&#10;City, State ZIP" required />
                                <span v-if="form.errors.billing_address" class="mt-1 block text-xs font-bold text-red-600">{{ form.errors.billing_address }}</span>
                            </label>

                            <label class="block">
                                <span class="field-label">Delivery address <span class="font-semibold text-slate-400">(optional)</span></span>
                                <textarea v-model.trim="form.shipping_address" class="field-input min-h-24 resize-none" maxlength="600" placeholder="Leave blank if same as billing" />
                                <span v-if="form.errors.shipping_address" class="mt-1 block text-xs font-bold text-red-600">{{ form.errors.shipping_address }}</span>
                            </label>
                        </div>

                        <p v-if="cartError" class="mt-4 text-sm font-bold text-red-600">{{ cartError }}</p>
                    </div>

                    <div class="shrink-0 px-5 pb-[max(1rem,env(safe-area-inset-bottom))] pt-2 sm:px-7 sm:pb-6">
                        <button class="btn-primary min-h-[52px] w-full" type="submit" :disabled="form.processing || !phoneValid || !form.billing_address.trim()">
                            <Send class="size-4" /> {{ form.processing ? 'Sending order…' : 'Send order to Tabarak' }}
                        </button>
                        <p class="mt-3 flex items-start justify-center gap-1.5 text-center text-[11px] font-semibold leading-4 text-slate-400">
                            <ShieldCheck class="mt-px size-3.5 shrink-0 text-tabarak-orange" />
                            <span>No returns on opened items; claims must follow the 30-day policy. <Link :href="route('policy')" class="font-extrabold text-tabarak-ink underline decoration-tabarak-orange/50 underline-offset-4">Read policy</Link></span>
                        </p>
                    </div>
                </form>
            </section>
        </Transition>
    </Teleport>
</template>
