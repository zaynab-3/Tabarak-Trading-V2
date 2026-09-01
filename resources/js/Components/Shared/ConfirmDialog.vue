<script setup lang="ts">
import { AlertTriangle, X } from '@lucide/vue';
import { onBeforeUnmount, watch } from 'vue';

const props = withDefaults(defineProps<{
    open: boolean;
    title: string;
    description: string;
    confirmLabel?: string;
    tone?: 'danger' | 'warning';
    processing?: boolean;
}>(), {
    confirmLabel: 'Confirm',
    tone: 'danger',
    processing: false,
});
const emit = defineEmits<{ confirm: []; cancel: [] }>();
const dialogTitleId = `confirm-dialog-${Math.random().toString(36).slice(2, 10)}`;

const onKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape' && props.open && !props.processing) emit('cancel');
};

watch(() => props.open, (open) => {
    if (open) document.addEventListener('keydown', onKeydown);
    else document.removeEventListener('keydown', onKeydown);
});
onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown));
</script>

<template>
    <Teleport to="body">
        <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-100 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="open" class="fixed inset-0 z-[100] grid place-items-center bg-slate-950/55 p-4 backdrop-blur-sm" role="presentation" @mousedown.self="!processing && emit('cancel')">
                <section class="w-full max-w-md overflow-hidden rounded-xl border border-white/40 bg-white shadow-[0_28px_80px_rgba(15,23,42,0.35)]" role="alertdialog" aria-modal="true" :aria-labelledby="dialogTitleId">
                    <div class="flex items-start gap-4 p-5 md:p-6">
                        <div class="grid size-11 shrink-0 place-items-center rounded-full" :class="tone === 'danger' ? 'bg-red-50 text-red-600' : 'bg-[#FFF0E8] text-tabarak-orange'">
                            <AlertTriangle class="size-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <h2 :id="dialogTitleId" class="font-display text-xl font-bold text-tabarak-ink">{{ title }}</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ description }}</p>
                        </div>
                        <button type="button" class="grid size-9 shrink-0 place-items-center rounded-md text-slate-400 transition hover:bg-tabarak-mist hover:text-tabarak-blue" aria-label="Close dialog" :disabled="processing" @click="emit('cancel')"><X class="size-5" /></button>
                    </div>
                    <div class="flex flex-col-reverse gap-2 border-t border-tabarak-line bg-tabarak-mist px-5 py-4 sm:flex-row sm:justify-end">
                        <button type="button" class="btn-secondary" :disabled="processing" @click="emit('cancel')">Cancel</button>
                        <button type="button" class="inline-flex min-h-11 items-center justify-center rounded-md px-5 text-sm font-bold text-white transition disabled:cursor-not-allowed disabled:opacity-50" :class="tone === 'danger' ? 'bg-red-600 hover:bg-red-700' : 'bg-tabarak-orange hover:bg-[#DD4900]'" :disabled="processing" @click="emit('confirm')">{{ processing ? 'Working…' : confirmLabel }}</button>
                    </div>
                </section>
            </div>
        </Transition>
    </Teleport>
</template>
