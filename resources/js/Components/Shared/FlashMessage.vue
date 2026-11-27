<script setup lang="ts">
import { CheckCircle2, CircleAlert, X } from '@lucide/vue';
import { onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps<{ success?: string; error?: string }>();
const visible = ref(Boolean(props.success || props.error));
let hideTimer: ReturnType<typeof setTimeout> | null = null;

const scheduleHide = () => {
    if (hideTimer) clearTimeout(hideTimer);
    visible.value = Boolean(props.success || props.error);
    if (visible.value) hideTimer = setTimeout(() => { visible.value = false; }, 5000);
};

watch(() => [props.success, props.error], scheduleHide, { immediate: true });
onBeforeUnmount(() => { if (hideTimer) clearTimeout(hideTimer); });
</script>

<template>
    <Transition enter-active-class="transition duration-200" enter-from-class="translate-y-2 opacity-0" leave-active-class="transition duration-200" leave-to-class="translate-y-2 opacity-0">
        <div
            v-if="visible && (success || error)"
            class="flex items-start gap-3 rounded-2xl px-4 py-3 text-sm text-white shadow-[0_14px_40px_rgba(25,27,32,0.18)]"
            :class="success ? 'bg-[#20362C]' : 'bg-[#8E2E2E]'"
            role="status"
        >
            <CheckCircle2 v-if="success" class="mt-0.5 size-5 shrink-0" aria-hidden="true" />
            <CircleAlert v-else class="mt-0.5 size-5 shrink-0" aria-hidden="true" />
            <p class="min-w-0 flex-1 font-semibold leading-5">{{ success || error }}</p>
            <button type="button" class="grid size-7 shrink-0 place-items-center rounded-full text-white/70 transition hover:bg-white/10 hover:text-white" aria-label="Dismiss notification" @click="visible = false">
                <X class="size-4" />
            </button>
        </div>
    </Transition>
</template>
