<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { Search, X } from '@lucide/vue';
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps<{ open: boolean }>();
const emit = defineEmits<{ close: [] }>();
const page = usePage();
const query = ref('');
const input = ref<HTMLInputElement | null>(null);
const isSearching = ref(false);
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

const currentSearch = computed(() => (page.props.filters as { search?: string } | undefined)?.search ?? '');

const close = () => emit('close');

const runSearch = (value: string) => {
    const currentParams = route().current('shop') || route().current('search')
        ? new URLSearchParams(window.location.search)
        : new URLSearchParams();

    if (value) currentParams.set('search', value);
    else currentParams.delete('search');
    currentParams.delete('page');

    router.get(route('shop'), Object.fromEntries(currentParams.entries()), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        showProgress: false,
        onStart: () => { isSearching.value = true; },
        onFinish: () => { isSearching.value = false; },
    });
};

const clearSearch = () => {
    query.value = '';
    input.value?.focus();
};

const onKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape') close();
};

watch(query, (value) => {
    if (!props.open) return;

    const normalized = value.trim();
    if (normalized === currentSearch.value.trim()) return;

    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => runSearch(normalized), 260);
});

watch(() => props.open, async (isOpen) => {
    document.body.style.overflow = isOpen ? 'hidden' : '';

    if (isOpen) {
        query.value = currentSearch.value;
        window.addEventListener('keydown', onKeydown);
        await nextTick();
        input.value?.focus();
    } else {
        if (searchTimeout) {
            clearTimeout(searchTimeout);
            searchTimeout = null;
        }
        window.removeEventListener('keydown', onKeydown);
    }
});

onBeforeUnmount(() => {
    document.body.style.overflow = '';
    if (searchTimeout) clearTimeout(searchTimeout);
    window.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <Teleport to="body">
        <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" leave-active-class="transition-opacity duration-150" leave-to-class="opacity-0">
            <div v-if="open" class="fixed inset-0 z-[90] bg-[#17191D]/38 backdrop-blur-[5px]" @click="close"></div>
        </Transition>

        <Transition
            enter-active-class="transition duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]"
            enter-from-class="translate-y-8 opacity-0 sm:scale-[0.98] sm:translate-y-3"
            leave-active-class="transition duration-180 ease-in"
            leave-to-class="translate-y-5 opacity-0 sm:scale-[0.98]"
        >
            <section
                v-if="open"
                class="fixed inset-x-3 bottom-3 z-[91] mx-auto flex min-h-[46dvh] w-auto max-w-xl flex-col rounded-[28px] bg-white px-5 pb-[max(1.25rem,env(safe-area-inset-bottom))] pt-4 shadow-[0_28px_90px_rgba(25,27,32,0.24)] sm:inset-x-6 sm:bottom-auto sm:top-1/2 sm:min-h-0 sm:-translate-y-1/2 sm:px-7 sm:pb-7 sm:pt-6"
                role="dialog"
                aria-modal="true"
                aria-labelledby="catalogue-search-title"
            >
                <span class="mx-auto mb-4 block h-1 w-10 rounded-full bg-[#D8D5CF] sm:hidden" aria-hidden="true"></span>

                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="eyebrow">Catalogue search</p>
                        <h2 id="catalogue-search-title" class="mt-1 font-display text-[1.65rem] font-bold tracking-[-0.04em] text-tabarak-ink sm:text-3xl">Find a product</h2>
                    </div>
                    <button type="button" class="grid size-10 shrink-0 place-items-center rounded-full text-slate-500 transition hover:bg-[#F2F0EB] hover:text-tabarak-ink" aria-label="Close search" @click="close">
                        <X class="size-5" />
                    </button>
                </div>

                <div class="mt-5">
                    <label for="catalogue-search" class="sr-only">Search products or SKU</label>
                    <div class="flex min-h-14 items-center gap-3 rounded-[18px] bg-[#F3F2ED] px-4 transition focus-within:bg-[#EEECE6]">
                        <Search class="size-5 shrink-0 text-slate-400" />
                        <input
                            id="catalogue-search"
                            ref="input"
                            v-model="query"
                            type="search"
                            class="h-14 min-w-0 flex-1 border-0 bg-transparent p-0 text-base font-semibold text-tabarak-ink placeholder:text-slate-400 focus:ring-0"
                            placeholder="Search product name or SKU"
                            autocomplete="off"
                            spellcheck="false"
                        />
                        <button v-if="query" type="button" class="grid size-9 shrink-0 place-items-center rounded-full text-slate-400 transition hover:bg-white hover:text-tabarak-ink" aria-label="Clear search" @click="clearSearch">
                            <X class="size-4" />
                        </button>
                    </div>
                    <p class="mt-3 text-xs font-semibold text-slate-400" aria-live="polite">
                        {{ isSearching ? 'Updating results…' : 'Results update automatically as you type.' }}
                    </p>
                </div>
            </section>
        </Transition>
    </Teleport>
</template>
