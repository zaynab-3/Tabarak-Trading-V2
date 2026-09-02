<script setup lang="ts">
import { RotateCcw } from '@lucide/vue';
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import SelectMenu, { type SelectOption } from '@/Components/Shared/SelectMenu.vue';
import { storefrontHeaderVisible } from '@/Composables/useAutoHideStorefrontHeader';
import type { ProductFilters } from '@/Composables/useProductFilters';
import type { TaxonomyRef } from '@/types/catalogue';

const props = defineProps<{
    filters: ProductFilters;
    categories: TaxonomyRef[];
    brands: TaxonomyRef[];
    total: number;
}>();
const emit = defineEmits<{ apply: []; reset: [] }>();

const navRef = ref<HTMLElement | null>(null);
const allProductsBtnRef = ref<HTMLElement | null>(null);
const categoryBtnRefs = ref<Record<string | number, HTMLElement | null>>({});

const selectedCategory = computed(() => props.categories.find(
    (category) => String(category.id) === String(props.filters.category),
) ?? null);
const brandOptions = computed<SelectOption[]>(() => [
    { value: '', label: props.filters.category ? 'All brands in category' : 'All brands' },
    ...props.brands.map((brand) => ({ value: brand.id, label: brand.name })),
]);
const sortOptions: SelectOption[] = [
    { value: 'newest', label: 'Newest' },
    { value: 'name-asc', label: 'Name A-Z' },
    { value: 'name-desc', label: 'Name Z-A' },
];

const centerElement = (el: HTMLElement | null | undefined, smooth = true) => {
    if (!el || !navRef.value) return;
    const nav = navRef.value;
    const buttonRect = el.getBoundingClientRect();
    const navRect = nav.getBoundingClientRect();
    const offset = (buttonRect.left - navRect.left) - (nav.clientWidth / 2) + (buttonRect.width / 2);

    nav.scrollTo({
        left: nav.scrollLeft + offset,
        behavior: smooth ? 'smooth' : 'auto',
    });
};

const centerActiveCategory = (smooth = true) => {
    nextTick(() => {
        if (!props.filters.category) {
            centerElement(allProductsBtnRef.value, smooth);
        } else {
            centerElement(categoryBtnRefs.value[props.filters.category], smooth);
        }
    });
};

const selectCategory = (categoryId: number | '', event?: MouseEvent) => {
    props.filters.category = categoryId;
    props.filters.brand = '';
    window.scrollTo({ top: 0, behavior: 'smooth' });
    emit('apply');

    if (event?.currentTarget instanceof HTMLElement) {
        centerElement(event.currentTarget, true);
    } else {
        centerActiveCategory(true);
    }
};

const handleReset = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    emit('reset');
};

onMounted(() => {
    if (props.filters.category) {
        centerActiveCategory(false);
    }
});

watch(() => props.filters.category, () => {
    centerActiveCategory(true);
});
</script>

<template>
    <section
        class="sticky z-40 border-b border-[#E7EAF3] bg-white/95 backdrop-blur transition-[top] duration-200 ease-out will-change-[top]"
        :class="storefrontHeaderVisible ? 'top-[5.5rem] md:top-16' : 'top-0'"
    >
        <nav
            ref="navRef"
            class="page-shell no-scrollbar flex min-h-10 items-center gap-1.5 overflow-x-auto border-b border-tabarak-line py-1.5 [scrollbar-width:none] [-ms-overflow-style:none] sm:min-h-11 sm:gap-2 sm:py-1.5 md:min-h-13 md:gap-2.5 md:py-2 lg:min-h-14 lg:gap-3 lg:py-2.5 [&::-webkit-scrollbar]:hidden"
            aria-label="Product categories"
        >
            <button
                ref="allProductsBtnRef"
                class="min-h-8 min-w-max rounded-full px-3.5 text-xs font-semibold transition sm:min-h-9 sm:px-4 sm:text-[13px] md:min-h-10 md:px-5 md:text-sm md:font-bold lg:min-h-11 lg:px-5.5 lg:text-[15px]"
                :class="!filters.category ? 'bg-tabarak-blue text-white shadow-sm' : 'bg-slate-100/90 text-slate-600 hover:bg-tabarak-mist hover:text-tabarak-blue'"
                type="button"
                @click="selectCategory('', $event)"
            >
                All products
            </button>
            <button
                v-for="category in categories"
                :key="category.id"
                :ref="(el) => { if (el) categoryBtnRefs[category.id] = el as HTMLElement; }"
                class="min-h-8 min-w-max rounded-full px-3.5 text-xs font-semibold transition sm:min-h-9 sm:px-4 sm:text-[13px] md:min-h-10 md:px-5 md:text-sm md:font-bold lg:min-h-11 lg:px-5.5 lg:text-[15px]"
                :class="String(filters.category) === String(category.id) ? 'bg-tabarak-blue text-white shadow-sm' : 'bg-slate-100/90 text-slate-600 hover:bg-tabarak-mist hover:text-tabarak-blue'"
                type="button"
                @click="selectCategory(category.id, $event)"
            >
                {{ category.name }}
            </button>
        </nav>

        <div class="page-shell flex min-h-11 items-center justify-between gap-2 py-1.5 sm:min-h-13 sm:py-2 md:min-h-14 md:py-2.5">
            <div class="mr-auto flex min-w-0 items-baseline gap-1.5 md:gap-2">
                <h1 class="truncate font-display text-sm font-bold text-tabarak-ink sm:text-base md:text-xl lg:text-2xl">{{ selectedCategory?.name || 'All products' }}</h1>
                <span class="shrink-0 text-[11px] font-medium text-slate-400 sm:text-xs md:text-sm">({{ total }})</span>
            </div>

            <div class="flex shrink-0 items-center gap-1.5 sm:gap-2 md:gap-2.5">
                <div class="w-28 sm:w-36 md:w-44 lg:w-48">
                    <SelectMenu compact v-model="filters.brand" :options="brandOptions" aria-label="Filter by brand" @change="emit('apply')" />
                </div>
                <div class="w-24 sm:w-32 md:w-36 lg:w-40">
                    <SelectMenu compact v-model="filters.sort" :options="sortOptions" aria-label="Sort products" @change="emit('apply')" />
                </div>
                <button class="grid size-8.5 shrink-0 place-items-center rounded-lg border border-tabarak-line bg-white text-tabarak-blue transition hover:border-tabarak-orange hover:text-tabarak-orange sm:size-9 md:size-10 lg:size-11" type="button" aria-label="Reset catalogue filters" title="Reset filters" @click="handleReset">
                    <RotateCcw class="size-3.5 sm:size-4 lg:size-4.5" />
                </button>
            </div>
        </div>
    </section>
</template>
