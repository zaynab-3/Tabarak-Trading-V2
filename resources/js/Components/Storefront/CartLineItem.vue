<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Image as ImageIcon, Minus, Plus, ShieldCheck, Trash2 } from '@lucide/vue';
import axios from 'axios';
import { computed, ref, watch } from 'vue';
import type { PageProps } from '@/types';
import type { CartItem, CartSummary } from '@/types/orders';
import { formatMoney, productPackLabel } from '@/Utils/format';

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

watch(() => props.item, (newItem) => {
    if (saveStatus.value === 'idle' && !isTypingPrice.value) {
        localItem.value = { ...newItem };
        customPriceInput.value = newItem.is_custom_price ? String(newItem.unit_price) : '';
    }
}, { deep: true });

const maximumQuantity = computed(() => props.item.product.tracks_stock ? Math.max(1, props.item.product.stock_quantity ?? 0) : 999);
const catalogPrice = computed<string>(() => String(localItem.value.original_unit_price || localItem.value.product.unit_price || '0.00'));
const toCents = (val: string | number): number => Math.round(Number(val) * 100);
const fromCents = (cents: number): string => (cents / 100).toFixed(2);

const recalculateAndEmit = () => {
    localItem.value.line_total = fromCents(toCents(localItem.value.unit_price) * localItem.value.quantity);
    emit('update:item', { ...localItem.value });
};

const syncToServer = async (resetCustomPrice = false) => {
    if (syncTimeout) { clearTimeout(syncTimeout); syncTimeout = null; }
    saveStatus.value = 'saving';
    errorMessage.value = null;

    try {
        const payload: { quantity: number; custom_unit_price?: string | null; reset_custom_price?: boolean } = {
            quantity: localItem.value.quantity,
            reset_custom_price: resetCustomPrice,
        };
        if (!resetCustomPrice && localItem.value.is_custom_price && customPriceInput.value !== '') payload.custom_unit_price = customPriceInput.value;

        const response = await axios.patch<{ success: boolean; cart: CartSummary }>(route('cart.items.update', props.item.product.slug), payload, { headers: { Accept: 'application/json' } });
        if (response.data.cart) {
            emit('synced', response.data.cart);
            saveStatus.value = 'saved';
            if (savedStatusTimeout) clearTimeout(savedStatusTimeout);
            savedStatusTimeout = setTimeout(() => { if (saveStatus.value === 'saved') saveStatus.value = 'idle'; }, 1600);
        }
    } catch (err: any) {
        saveStatus.value = 'idle';
        errorMessage.value = err.response?.data?.message || Object.values(err.response?.data?.errors || {}).flat().join(' ') || 'Failed to update item.';
    }
};

const debounceSync = (delay = 350, resetCustomPrice = false) => {
    if (syncTimeout) clearTimeout(syncTimeout);
    syncTimeout = setTimeout(() => syncToServer(resetCustomPrice), delay);
};

const changeQuantity = (newQty: number) => {
    const qty = Math.max(1, Math.min(maximumQuantity.value, newQty));
    if (qty === localItem.value.quantity) return;
    localItem.value.quantity = qty;
    recalculateAndEmit();
    debounceSync();
};

const onQuantityInput = (event: Event) => {
    const parsed = parseInt((event.target as HTMLInputElement).value, 10);
    if (Number.isNaN(parsed) || parsed < 1) return;
    localItem.value.quantity = Math.max(1, Math.min(maximumQuantity.value, parsed));
    recalculateAndEmit();
    debounceSync(450);
};

const onPriceInput = () => {
    isTypingPrice.value = true;
    const val = parseFloat(customPriceInput.value);
    if (!Number.isNaN(val) && val > 0) {
        localItem.value.unit_price = val.toFixed(2);
        localItem.value.is_custom_price = true;
        recalculateAndEmit();
        debounceSync(600);
    }
};

const onPriceBlur = () => {
    isTypingPrice.value = false;
    const val = parseFloat(customPriceInput.value);
    if (!Number.isNaN(val) && val > 0) {
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
    if (isRemoving.value) return;
    isRemoving.value = true;
    emit('remove', localItem.value.product.id);
    try {
        const response = await axios.delete<{ success: boolean; cart: CartSummary }>(route('cart.items.destroy', localItem.value.product.slug), { headers: { Accept: 'application/json' } });
        if (response.data.cart) emit('synced', response.data.cart);
    } catch {
        isRemoving.value = false;
        errorMessage.value = 'Could not remove this item.';
    }
};
</script>

<template>
    <article class="grid grid-cols-[84px_minmax(0,1fr)] gap-3 rounded-[22px] bg-white p-3 sm:grid-cols-[96px_minmax(0,1fr)] sm:gap-4 sm:p-4">
        <div class="grid size-[84px] place-items-center self-start overflow-hidden rounded-[18px] bg-[#F2F1EC] p-2.5 sm:size-24">
            <img v-if="localItem.product.primary_image" :src="localItem.product.primary_image.url" :alt="localItem.product.primary_image.alt_text || localItem.product.name" class="max-h-full max-w-full object-contain" />
            <ImageIcon v-else class="size-6 text-slate-300" />
        </div>

        <div class="min-w-0">
            <p class="truncate text-[9px] font-extrabold uppercase tracking-[0.14em] text-tabarak-orange sm:text-[10px]">
                {{ localItem.product.category?.name || localItem.product.brand?.name || 'Catalogue' }}
            </p>

            <div class="mt-1 flex items-start justify-between gap-2">
                <h2 class="min-w-0 flex-1 font-display text-[15px] font-bold leading-[1.25] tracking-[-0.02em] text-tabarak-ink sm:text-lg">
                    {{ localItem.product.name }}
                </h2>
                <button
                    type="button"
                    class="-mr-1 -mt-1 grid size-9 shrink-0 place-items-center rounded-full text-slate-400 transition hover:bg-red-50 hover:text-red-600 disabled:opacity-50"
                    :disabled="isRemoving"
                    aria-label="Remove item"
                    @click="remove"
                >
                    <Trash2 class="size-4" />
                </button>
            </div>

            <p class="mt-1 text-[11px] font-semibold text-slate-400 sm:text-xs">{{ productPackLabel(localItem.product) }}</p>

            <div v-if="isAdmin" class="mt-3 rounded-[16px] bg-[#FFF4DF] px-3 py-2.5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="flex items-center gap-1.5 text-[9px] font-extrabold uppercase tracking-[0.12em] text-amber-900"><ShieldCheck class="size-3.5" /> Admin price</span>
                    <button v-if="localItem.is_custom_price" type="button" class="text-[10px] font-bold text-amber-900 underline underline-offset-2" @click="resetPrice">Reset ${{ catalogPrice }}</button>
                </div>
                <div class="mt-2 flex items-center gap-2">
                    <div class="relative w-28">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-slate-400">$</span>
                        <input v-model="customPriceInput" class="field-input h-9 min-h-9 py-1 pl-7 text-xs font-bold" type="number" step="0.01" min="0.01" max="999999.99" :placeholder="catalogPrice" @input="onPriceInput" @blur="onPriceBlur" @keydown.enter.prevent="onPriceBlur" />
                    </div>
                    <span v-if="saveStatus === 'saving'" class="text-[10px] font-semibold text-slate-500">Saving…</span>
                    <span v-else-if="saveStatus === 'saved'" class="text-[10px] font-bold text-emerald-700">Saved</span>
                </div>
            </div>

            <div class="mt-3 flex items-end justify-between gap-3">
                <div class="inline-flex h-9 items-center rounded-full bg-[#F0EFEA] px-0.5">
                    <button type="button" class="grid size-8 place-items-center rounded-full text-slate-500 transition hover:bg-white hover:text-tabarak-ink disabled:opacity-30" :disabled="localItem.quantity <= 1" aria-label="Decrease quantity" @click="changeQuantity(localItem.quantity - 1)"><Minus class="size-3.5" /></button>
                    <input :value="localItem.quantity" class="h-8 w-9 border-0 bg-transparent p-0 text-center text-sm font-extrabold text-tabarak-ink focus:ring-0 [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:m-0 [&::-webkit-outer-spin-button]:m-0" type="number" min="1" :max="maximumQuantity" @input="onQuantityInput" @change="onQuantityInput" />
                    <button type="button" class="grid size-8 place-items-center rounded-full text-slate-500 transition hover:bg-white hover:text-tabarak-ink disabled:opacity-30" :disabled="localItem.quantity >= maximumQuantity" aria-label="Increase quantity" @click="changeQuantity(localItem.quantity + 1)"><Plus class="size-3.5" /></button>
                </div>

                <div class="min-w-0 text-right">
                    <p class="font-display text-lg font-bold tracking-[-0.03em] text-tabarak-ink sm:text-xl">{{ formatMoney(localItem.line_total) }}</p>
                    <p v-if="localItem.quantity > 1" class="mt-0.5 text-[10px] font-semibold text-slate-400">{{ formatMoney(localItem.unit_price) }} each</p>
                </div>
            </div>

            <p v-if="localItem.product.tracks_stock" class="mt-2 text-[10px] font-semibold text-slate-400">{{ localItem.product.stock_quantity }} available</p>
            <p v-if="errorMessage" class="mt-2 text-xs font-bold text-red-600">{{ errorMessage }}</p>
        </div>
    </article>
</template>
