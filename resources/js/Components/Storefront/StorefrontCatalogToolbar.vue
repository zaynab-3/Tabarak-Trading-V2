<script setup lang="ts">
import { Filter, RotateCcw, X } from '@lucide/vue';
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import SelectMenu, { type SelectOption } from '@/Components/Shared/SelectMenu.vue';
import type { ProductFilters } from '@/Composables/useProductFilters';
import type { TaxonomyRef } from '@/types/catalogue';

const props = defineProps<{
    filters: ProductFilters;
    categories: TaxonomyRef[];
    brands: TaxonomyRef[];
    total: number;
}>();
const emit = defineEmits<{ apply: []; reset: [] }>();

const filtersOpen = ref(false);
const categoryNav = ref<HTMLElement | null>(null);
const selectedCategory = computed(() => props.categories.find((category) => String(category.id) === String(props.filters.category)) ?? null);
const brandOptions = computed<SelectOption[]>(() => [
    { value: '', label: props.filters.category ? 'All brands in category' : 'All brands' },
    ...props.brands.map((brand) => ({ value: brand.id, label: brand.name })),
]);
const sortOptions: SelectOption[] = [
    { value: 'newest', label: 'Newest' },
    { value: 'name-asc', label: 'Name A–Z' },
    { value: 'name-desc', label: 'Name Z–A' },
];

const scrollActiveCategoryIntoView = async (smooth = true) => {
    await nextTick();
    const id = props.filters.category ? String(props.filters.category) : 'all';
    const active = categoryNav.value?.querySelector<HTMLElement>(`[data-category-id="${id}"]`);
    active?.scrollIntoView({
        behavior: smooth ? 'smooth' : 'auto',
        block: 'nearest',
        inline: 'center',
    });
};

const selectCategory = (categoryId: number | '') => {
    props.filters.category = categoryId;
    props.filters.brand = '';
    emit('apply');
    void scrollActiveCategoryIntoView(true);
};

const closeFilters = () => { filtersOpen.value = false; };
const applyAndClose = () => { emit('apply'); closeFilters(); };
const resetAndClose = () => { emit('reset'); closeFilters(); };
const onKeydown = (event: KeyboardEvent) => { if (event.key === 'Escape') closeFilters(); };

watch(
    () => props.filters.category,
    () => { void scrollActiveCategoryIntoView(true); },
    { immediate: true },
);

watch(filtersOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
    if (open) window.addEventListener('keydown', onKeydown);
    else window.removeEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => {
    document.body.style.overflow = '';
    window.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <section class="page-shell pt-6 sm:pt-8 lg:pt-10">
        <div class="flex items-end justify-between gap-4">
            <div class="min-w-0">
                <p class="eyebrow">Catalogue</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <h1 class="truncate font-display text-[clamp(1.8rem,4vw,3rem)] font-bold tracking-[-0.04em] text-tabarak-ink">{{ selectedCategory?.name || 'All products' }}</h1>
                    <span class="shrink-0 text-sm font-semibold text-slate-400">{{ total }}</span>
                </div>
            </div>

            <div class="flex shrink-0 items-center gap-2 lg:hidden">
                <button type="button" class="inline-flex min-h-11 items-center gap-2 rounded-full bg-[#ECEAE5] px-4 text-sm font-bold text-tabarak-ink transition hover:bg-[#E2DFD8]" @click="filtersOpen = true">
                    <Filter class="size-4" /> Filter
                </button>
            </div>

            <div class="hidden shrink-0 items-center gap-2 lg:flex">
                <div class="w-48"><SelectMenu compact v-model="filters.brand" :options="brandOptions" aria-label="Filter by brand" @change="emit('apply')" /></div>
                <div class="w-40"><SelectMenu compact v-model="filters.sort" :options="sortOptions" aria-label="Sort products" @change="emit('apply')" /></div>
                <button type="button" class="inline-flex min-h-10 items-center gap-1.5 px-2 text-xs font-extrabold text-slate-500 transition hover:text-tabarak-orange" @click="emit('reset')">
                    <RotateCcw class="size-3.5" /> Reset
                </button>
            </div>
        </div>

        <nav ref="categoryNav" class="no-scrollbar mt-6 flex scroll-px-5 gap-6 overflow-x-auto pb-1" aria-label="Product categories">
            <button
                type="button"
                data-category-id="all"
                class="relative min-w-max pb-3 text-sm font-bold transition"
                :class="!filters.category ? 'text-tabarak-ink' : 'text-slate-400 hover:text-tabarak-ink'"
                @click="selectCategory('')"
            >
                All products
                <span v-if="!filters.category" class="absolute inset-x-0 bottom-0 h-0.5 rounded-full bg-tabarak-orange"></span>
            </button>
            <button
                v-for="category in categories"
                :key="category.id"
                type="button"
                :data-category-id="String(category.id)"
                class="relative min-w-max pb-3 text-sm font-bold transition"
                :class="String(filters.category) === String(category.id) ? 'text-tabarak-ink' : 'text-slate-400 hover:text-tabarak-ink'"
                @click="selectCategory(category.id)"
            >
                {{ category.name }}
                <span v-if="String(filters.category) === String(category.id)" class="absolute inset-x-0 bottom-0 h-0.5 rounded-full bg-tabarak-orange"></span>
            </button>
        </nav>

        <div v-if="filters.search" class="mt-4 flex items-center gap-2 text-sm text-slate-500">
            <span>Search:</span>
            <span class="font-bold text-tabarak-ink">“{{ filters.search }}”</span>
            <button type="button" class="font-extrabold text-tabarak-orange hover:underline" @click="filters.search = ''; emit('apply')">Clear</button>
        </div>
    </section>

    <Teleport to="body">
        <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" leave-active-class="transition-opacity duration-150" leave-to-class="opacity-0">
            <div v-if="filtersOpen" class="fixed inset-0 z-[90] bg-[#17191D]/38 backdrop-blur-[5px] lg:hidden" @click="closeFilters"></div>
        </Transition>
        <Transition enter-active-class="transition duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]" enter-from-class="translate-y-8 opacity-0" leave-active-class="transition duration-180 ease-in" leave-to-class="translate-y-6 opacity-0">
            <section
                v-if="filtersOpen"
                class="no-scrollbar fixed inset-x-3 bottom-3 z-[91] mx-auto max-h-[72dvh] w-auto max-w-xl overflow-y-auto rounded-[28px] bg-white px-5 pb-[max(1.25rem,env(safe-area-inset-bottom))] pt-3 shadow-[0_28px_90px_rgba(25,27,32,0.24)] sm:inset-x-6 sm:bottom-6 sm:px-6 lg:hidden"
                role="dialog"
                aria-modal="true"
                aria-labelledby="catalogue-filter-title"
            >
                <span class="mx-auto block h-1 w-10 rounded-full bg-[#D8D5CF]"></span>
                <div class="mt-3 flex items-center justify-between">
                    <h2 id="catalogue-filter-title" class="font-display text-2xl font-bold tracking-[-0.03em] text-tabarak-ink">Filter catalogue</h2>
                    <button type="button" class="grid size-10 place-items-center rounded-full text-slate-500 transition hover:bg-[#F2F0EB]" aria-label="Close filters" @click="closeFilters"><X class="size-5" /></button>
                </div>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <label class="block"><span class="field-label">Brand</span><SelectMenu v-model="filters.brand" :options="brandOptions" aria-label="Filter by brand" /></label>
                    <label class="block"><span class="field-label">Sort</span><SelectMenu v-model="filters.sort" :options="sortOptions" aria-label="Sort products" /></label>
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="button" class="btn-secondary flex-1" @click="resetAndClose">Reset</button>
                    <button type="button" class="btn-primary flex-1" @click="applyAndClose">Show products</button>
                </div>
            </section>
        </Transition>
    </Teleport>
</template>
