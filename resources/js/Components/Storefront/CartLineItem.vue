<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Image as ImageIcon, Minus, Plus, ShieldCheck, Trash2 } from '@lucide/vue';
import type { PageProps } from '@/types';
import type { CartItem, CartSummary } from '@/types/orders';
import { formatMoney, productPackLabel } from '@/Utils/format';
import { computed, ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps<{ item: CartItem }>();
const emit = defineEmits<{
    (e: 'update:item', item: CartItem): void;
    (e: 'synced', cart: CartSummary): void;
    (e: 'remove', productId: number): void;
}>();

const page = usePage<PageProps>();
const isAdmin = computed(() => Boolean(page.props.auth?.user?.is_admin));

const localItem = ref<CartItem>({ ...props.item });
const customPriceInput = ref<string>(props.item.is_custom_price ? String(props.item.unit_price) : '');
const isTypingPrice = ref(false);
const saveStatus = ref<'idle' | 'saving' | 'saved'>('idle');
const errorMessage = ref<string | null>(null);

let syncTimeout: ReturnType<typeof setTimeout> | null = null;
let savedStatusTimeout: ReturnType<typeof setTimeout> | null = null;

watch(
    () => props.item,
    (newItem) => {
        if (saveStatus.value === 'idle' && !isTypingPrice.value) {
            localItem.value = { ...newItem };
            customPriceInput.value = newItem.is_custom_price ? String(newItem.unit_price) : '';
        }
    },
    { deep: true },
);

const maximumQuantity = computed(() =>
    props.item.product.tracks_stock ? Math.max(1, props.item.product.stock_quantity ?? 0) : 999,
);

const catalogPrice = computed<string>(() =>
    String(localItem.value.original_unit_price || localItem.value.product.unit_price || '0.00'),
);

const toCents = (val: string | number): number => Math.round(Number(val) * 100);
const fromCents = (cents: number): string => (cents / 100).toFixed(2);

const recalculateAndEmit = () => {
    const unitCents = toCents(localItem.value.unit_price);
    const lineCents = unitCents * localItem.value.quantity;
    localItem.value.line_total = fromCents(lineCents);
    emit('update:item', { ...localItem.value });
};

const syncToServer = async (resetCustomPrice = false) => {
    if (syncTimeout) {
        clearTimeout(syncTimeout);
        syncTimeout = null;
    }

    saveStatus.value = 'saving';
    errorMessage.value = null;

    try {
        const payload: { quantity: number; custom_unit_price?: string | null; reset_custom_price?: boolean } = {
            quantity: localItem.value.quantity,
            reset_custom_price: resetCustomPrice,
        };

        if (!resetCustomPrice && localItem.value.is_custom_price && customPriceInput.value !== '') {
            payload.custom_unit_price = customPriceInput.value;
        }

        const response = await axios.patch<{ success: boolean; cart: CartSummary }>(
            route('cart.items.update', props.item.product.slug),
            payload,
            { headers: { Accept: 'application/json' } },
        );

        if (response.data.cart) {
            emit('synced', response.data.cart);
            saveStatus.value = 'saved';
            if (savedStatusTimeout) clearTimeout(savedStatusTimeout);
            savedStatusTimeout = setTimeout(() => {
                if (saveStatus.value === 'saved') saveStatus.value = 'idle';
            }, 1800);
        }
    } catch (err: any) {
        saveStatus.value = 'idle';
        if (err.response?.data?.message) {
            errorMessage.value = err.response.data.message;
        } else if (err.response?.data?.errors) {
            const errs = err.response.data.errors;
            errorMessage.value = Object.values(errs).flat().join(' ');
        } else {
            errorMessage.value = 'Failed to update item.';
        }
    }
};

const debounceSync = (delay = 350, resetCustomPrice = false) => {
    if (syncTimeout) clearTimeout(syncTimeout);
    syncTimeout = setTimeout(() => {
        syncToServer(resetCustomPrice);
    }, delay);
};

const changeQuantity = (newQty: number) => {
    const qty = Math.max(1, Math.min(maximumQuantity.value, newQty));
    if (qty === localItem.value.quantity) return;

    localItem.value.quantity = qty;
    recalculateAndEmit();
    debounceSync(350);
};

const onQuantityInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const parsed = parseInt(target.value, 10);
    if (isNaN(parsed) || parsed < 1) return;

    const qty = Math.max(1, Math.min(maximumQuantity.value, parsed));
    localItem.value.quantity = qty;
    recalculateAndEmit();
    debounceSync(450);
};

const onPriceInput = () => {
    isTypingPrice.value = true;
    const val = parseFloat(customPriceInput.value);

    if (!isNaN(val) && val > 0) {
        localItem.value.unit_price = val.toFixed(2);
        localItem.value.is_custom_price = true;
        recalculateAndEmit();
        debounceSync(600);
    }
};

const onPriceBlur = () => {
    isTypingPrice.value = false;
    const val = parseFloat(customPriceInput.value);

    if (!isNaN(val) && val > 0) {
        customPriceInput.value = val.toFixed(2);
        localItem.value.unit_price = val.toFixed(2);
        localItem.value.is_custom_price = true;
        recalculateAndEmit();
        syncToServer(false);
    } else if (customPriceInput.value === '' && localItem.value.is_custom_price) {
        resetPrice();
    }
};

const resetPrice = () => {
    isTypingPrice.value = false;
    customPriceInput.value = '';
    localItem.value.unit_price = catalogPrice.value;
    localItem.value.is_custom_price = false;
    recalculateAndEmit();
    syncToServer(true);
};

const isRemoving = ref(false);
const remove = async () => {
    isRemoving.value = true;
    emit('remove', localItem.value.product.id);

    try {
        const response = await axios.delete<{ success: boolean; cart: CartSummary }>(
            route('cart.items.destroy', localItem.value.product.slug),
            { headers: { Accept: 'application/json' } },
        );
        if (response.data.cart) {
            emit('synced', response.data.cart);
        }
    } catch {
        isRemoving.value = false;
    }
};
</script>

<template>
    <article class="grid gap-4 rounded-lg border border-tabarak-line bg-white p-4 sm:grid-cols-[132px_minmax(0,1fr)] sm:p-5">
        <div class="grid min-h-32 place-items-center overflow-hidden rounded-md bg-tabarak-mist p-3">
            <img v-if="localItem.product.primary_image" :src="localItem.product.primary_image.url" :alt="localItem.product.primary_image.alt_text || localItem.product.name" class="h-28 w-full object-contain" />
            <ImageIcon v-else class="size-8 text-slate-300" />
        </div>
        <div class="min-w-0">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-tabarak-orange">{{ localItem.product.brand?.name || 'Tabarak selection' }}</p>
                    <h2 class="mt-1 font-display text-xl font-bold leading-tight text-tabarak-ink">{{ localItem.product.name }}</h2>
                    <p class="mt-2 text-sm text-slate-500">{{ productPackLabel(localItem.product) }}</p>
                </div>
                <button
                    type="button"
                    class="grid size-10 shrink-0 place-items-center rounded-md border border-red-200 text-red-600 transition hover:bg-red-50 disabled:opacity-50"
                    :disabled="isRemoving"
                    aria-label="Remove item"
                    @click="remove"
                >
                    <Trash2 class="size-4" />
                </button>
            </div>

            <div v-if="isAdmin" class="mt-4 rounded-md border border-amber-200 bg-amber-50/70 p-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-amber-900">
                        <ShieldCheck class="size-3.5 text-amber-700" /> Admin unit price override
                    </span>
                    <button
                        v-if="localItem.is_custom_price"
                        type="button"
                        class="text-xs font-semibold text-amber-800 underline hover:text-amber-950 disabled:opacity-50"
                        @click="resetPrice"
                    >
                        Reset to catalog (${{ catalogPrice }})
                    </button>
                </div>
                <div class="mt-2 flex flex-wrap items-center gap-2.5">
                    <div class="relative w-32">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-xs font-bold text-slate-400">$</span>
                        <input
                            v-model="customPriceInput"
                            class="field-input h-8 py-1 pl-6 text-xs font-semibold"
                            type="number"
                            step="0.01"
                            min="0.01"
                            max="999999.99"
                            :placeholder="catalogPrice"
                            @input="onPriceInput"
                            @blur="onPriceBlur"
                            @keydown.enter.prevent="onPriceBlur"
                        />
                    </div>
                    <span v-if="saveStatus === 'saving'" class="flex items-center gap-1.5 text-xs font-medium text-slate-500">
                        <span class="size-1.5 animate-ping rounded-full bg-tabarak-blue"></span>
                        Saving…
                    </span>
                    <span v-else-if="saveStatus === 'saved'" class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600">
                        ✓ Saved
                    </span>
                </div>
                <p v-if="errorMessage" class="mt-1 text-xs font-semibold text-red-600">{{ errorMessage }}</p>
            </div>

            <div class="mt-5 flex flex-wrap items-end justify-between gap-4 border-t border-tabarak-line pt-4">
                <div>
                    <span class="field-label mb-1.5 block">Quantity</span>
                    <div class="inline-flex items-center rounded-md border border-tabarak-line bg-white shadow-sm">
                        <button
                            type="button"
                            class="grid size-9 place-items-center text-slate-600 hover:bg-tabarak-mist hover:text-tabarak-ink rounded-l-md transition disabled:opacity-30 disabled:cursor-not-allowed"
                            :disabled="localItem.quantity <= 1"
                            aria-label="Decrease quantity"
                            @click="changeQuantity(localItem.quantity - 1)"
                        >
                            <Minus class="size-3.5" />
                        </button>
                        <input
                            :value="localItem.quantity"
                            class="h-9 w-14 border-0 p-0 text-center text-sm font-bold text-tabarak-ink focus:ring-0"
                            type="number"
                            min="1"
                            :max="maximumQuantity"
                            @input="onQuantityInput"
                            @change="onQuantityInput"
                        />
                        <button
                            type="button"
                            class="grid size-9 place-items-center text-slate-600 hover:bg-tabarak-mist hover:text-tabarak-ink rounded-r-md transition disabled:opacity-30 disabled:cursor-not-allowed"
                            :disabled="localItem.quantity >= maximumQuantity"
                            aria-label="Increase quantity"
                            @click="changeQuantity(localItem.quantity + 1)"
                        >
                            <Plus class="size-3.5" />
                        </button>
                    </div>
                </div>

                <p v-if="localItem.product.tracks_stock" class="text-xs font-semibold text-slate-500">{{ localItem.product.stock_quantity }} currently available</p>

                <div class="text-right">
                    <div v-if="isAdmin && localItem.is_custom_price" class="mb-1 flex flex-col items-end gap-0.5">
                        <span class="inline-flex items-center gap-1 rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-amber-900">
                            Custom price
                        </span>
                        <span v-if="localItem.original_unit_price" class="text-[11px] text-slate-400 line-through">
                            Reg. {{ formatMoney(localItem.original_unit_price) }}
                        </span>
                    </div>
                    <p class="text-xs font-semibold text-slate-600">{{ formatMoney(localItem.unit_price) }} each</p>
                    <p class="mt-1 text-xl font-bold text-tabarak-blue">{{ formatMoney(localItem.line_total) }}</p>
                </div>
            </div>
        </div>
    </article>
</template>
