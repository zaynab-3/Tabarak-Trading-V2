<script setup lang="ts">
import { Check, ChevronDown } from '@lucide/vue';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

export interface SelectOption {
    value: string | number;
    label: string;
    description?: string;
}

const props = withDefaults(defineProps<{
    modelValue?: string | number | null;
    options: SelectOption[];
    placeholder?: string;
    ariaLabel?: string;
    disabled?: boolean;
    compact?: boolean;
}>(), {
    modelValue: '',
    placeholder: 'Select an option',
    ariaLabel: 'Select an option',
    disabled: false,
    compact: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string | number];
    change: [value: string | number];
}>();

const root = ref<HTMLElement | null>(null);
const optionButtons = ref<HTMLButtonElement[]>([]);
const open = ref(false);
const activeIndex = ref(0);
const menuId = `select-menu-${Math.random().toString(36).slice(2, 10)}`;
const selectedIndex = computed(() => props.options.findIndex((option) => String(option.value) === String(props.modelValue ?? '')));
const selected = computed(() => props.options[selectedIndex.value] ?? null);

const close = () => { open.value = false; };
const focusOption = async (index: number) => {
    if (!props.options.length) return;
    activeIndex.value = (index + props.options.length) % props.options.length;
    await nextTick();
    optionButtons.value[activeIndex.value]?.focus();
};
const toggle = async () => {
    if (props.disabled) return;
    open.value = !open.value;
    if (open.value) await focusOption(selectedIndex.value >= 0 ? selectedIndex.value : 0);
};
const choose = (option: SelectOption) => {
    emit('update:modelValue', option.value);
    emit('change', option.value);
    close();
};
const onTriggerKeydown = (event: KeyboardEvent) => {
    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault();
        open.value = true;
        focusOption(selectedIndex.value >= 0 ? selectedIndex.value : 0);
    }
};
const onOptionKeydown = (event: KeyboardEvent, index: number) => {
    if (event.key === 'ArrowDown') { event.preventDefault(); focusOption(index + 1); }
    if (event.key === 'ArrowUp') { event.preventDefault(); focusOption(index - 1); }
    if (event.key === 'Home') { event.preventDefault(); focusOption(0); }
    if (event.key === 'End') { event.preventDefault(); focusOption(props.options.length - 1); }
    if (event.key === 'Escape') { event.preventDefault(); close(); root.value?.querySelector<HTMLButtonElement>('[data-select-trigger]')?.focus(); }
};
const onDocumentClick = (event: MouseEvent) => {
    if (open.value && root.value && !root.value.contains(event.target as Node)) close();
};

watch(() => props.options, () => { if (activeIndex.value >= props.options.length) activeIndex.value = 0; });
onMounted(() => document.addEventListener('mousedown', onDocumentClick));
onBeforeUnmount(() => document.removeEventListener('mousedown', onDocumentClick));
</script>

<template>
    <div ref="root" class="relative min-w-0">
        <button
            data-select-trigger
            type="button"
            class="flex w-full items-center justify-between bg-[#F1EFEA] text-left font-bold text-tabarak-ink transition hover:bg-[#E8E5DE] focus:bg-white disabled:cursor-not-allowed disabled:opacity-50"
            :class="[
                compact ? 'min-h-10 rounded-full px-3.5 py-1 text-xs md:text-sm' : 'min-h-12 rounded-xl px-3.5 py-2 text-sm',
                open ? 'bg-white ring-2 ring-tabarak-ink/10' : ''
            ]"
            :aria-label="ariaLabel"
            :aria-expanded="open"
            :aria-controls="menuId"
            aria-haspopup="listbox"
            :disabled="disabled"
            @click="toggle"
            @keydown="onTriggerKeydown"
        >
            <span class="truncate" :class="selected ? '' : 'text-slate-500'">{{ selected?.label || placeholder }}</span>
            <ChevronDown class="shrink-0 text-slate-500 transition" :class="[compact ? 'size-3.5' : 'size-4', open ? 'rotate-180' : '']" />
        </button>

        <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="-translate-y-1 opacity-0" leave-active-class="transition duration-100 ease-in" leave-to-class="opacity-0">
            <div v-if="open" :id="menuId" role="listbox" :aria-label="ariaLabel" class="absolute right-0 z-[80] mt-2 max-h-72 min-w-full overflow-y-auto rounded-2xl bg-white p-1.5 shadow-[0_20px_60px_rgba(25,27,32,0.16)]">
                <button
                    v-for="(option, index) in options"
                    :key="String(option.value)"
                    :ref="(element) => { if (element) optionButtons[index] = element as HTMLButtonElement; }"
                    type="button"
                    role="option"
                    :aria-selected="String(option.value) === String(modelValue ?? '')"
                    class="flex w-full items-center justify-between rounded-xl text-left transition"
                    :class="[
                        compact ? 'min-h-9 px-3 py-1 text-xs' : 'min-h-11 px-3 py-2 text-sm',
                        String(option.value) === String(modelValue ?? '') ? 'bg-[#F1EFEA] font-extrabold text-tabarak-ink' : 'text-slate-600 hover:bg-[#F8F7F4] hover:text-tabarak-ink'
                    ]"
                    @click="choose(option)"
                    @keydown="onOptionKeydown($event, index)"
                >
                    <span class="min-w-0">
                        <span class="block truncate">{{ option.label }}</span>
                        <span v-if="option.description" class="mt-0.5 block text-[11px] font-normal text-slate-500">{{ option.description }}</span>
                    </span>
                    <Check v-if="String(option.value) === String(modelValue ?? '')" class="size-4 shrink-0 text-tabarak-orange" />
                </button>
            </div>
        </Transition>
    </div>
</template>
