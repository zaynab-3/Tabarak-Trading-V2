<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { FileX2, RotateCcw, Trash2, X } from '@lucide/vue';
import { computed, onBeforeUnmount, watch } from 'vue';
import type { Order, OrderDeletionMode } from '@/types/orders';

const props = defineProps<{ open: boolean; order: Order | null }>();
const emit = defineEmits<{ close: []; finished: [] }>();
const form = useForm<{ deletion_mode: OrderDeletionMode }>({ deletion_mode: 'delete_record_only' });
const dialogTitleId = `delete-order-${Math.random().toString(36).slice(2, 10)}`;
const hasReservedStock = computed(() => (props.order?.reserved_stock_quantity ?? 0) > 0);

const close = () => {
    if (!form.processing) emit('close');
};

const submit = () => {
    if (!props.order || form.processing) return;
    form.delete(route('admin.orders.destroy', props.order.public_token), {
        onSuccess: () => emit('finished'),
    });
};

const onKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape' && props.open) close();
};

watch(() => props.open, (open) => {
    if (open) {
        form.deletion_mode = props.order?.status === 'pending' && hasReservedStock.value
            ? 'cancel_restore_stock'
            : 'delete_record_only';
        document.addEventListener('keydown', onKeydown);
    } else {
        document.removeEventListener('keydown', onKeydown);
    }
});
onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown));
</script>

<template>
    <Teleport to="body">
        <div v-if="open && order" class="fixed inset-0 z-[100] grid place-items-center bg-slate-950/55 p-4 backdrop-blur-sm" role="presentation" @mousedown.self="close">
            <section class="w-full max-w-xl overflow-hidden rounded-xl border border-white/40 bg-white shadow-[0_28px_80px_rgba(15,23,42,0.35)]" role="alertdialog" aria-modal="true" :aria-labelledby="dialogTitleId">
                <div class="flex items-start gap-4 p-5 md:p-6">
                    <div class="grid size-11 shrink-0 place-items-center rounded-full bg-red-50 text-red-600"><Trash2 class="size-5" /></div>
                    <div class="min-w-0 flex-1">
                        <h2 :id="dialogTitleId" class="font-display text-xl font-bold text-tabarak-ink">Delete {{ order.order_number }}?</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Choose what happens to inventory. A permanent notice with the customer, items, quantities, totals, and your choice will remain in the admin panel.</p>
                    </div>
                    <button type="button" class="grid size-9 shrink-0 place-items-center rounded-md text-slate-400 hover:bg-tabarak-mist hover:text-tabarak-blue" aria-label="Close dialog" :disabled="form.processing" @click="close"><X class="size-5" /></button>
                </div>

                <div class="space-y-3 px-5 pb-5 md:px-6 md:pb-6">
                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border p-4 transition" :class="form.deletion_mode === 'cancel_restore_stock' ? 'border-tabarak-blue bg-blue-50/60' : 'border-tabarak-line'">
                        <input v-model="form.deletion_mode" type="radio" value="cancel_restore_stock" class="mt-1 text-tabarak-blue focus:ring-tabarak-blue" :disabled="!hasReservedStock || form.processing" />
                        <RotateCcw class="mt-0.5 size-5 shrink-0 text-tabarak-blue" />
                        <span><strong class="block text-sm text-tabarak-ink">Cancel order and return reserved stock</strong><span class="mt-1 block text-xs leading-5 text-slate-600">Restores {{ order.reserved_stock_quantity }} reserved unit{{ order.reserved_stock_quantity === 1 ? '' : 's' }}. Open-quantity and untracked products remain unchanged.</span><span v-if="!hasReservedStock" class="mt-1 block text-xs font-bold text-slate-400">This order has no tracked stock to restore.</span></span>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border p-4 transition" :class="form.deletion_mode === 'delete_record_only' ? 'border-tabarak-orange bg-[#FFF4EE]' : 'border-tabarak-line'">
                        <input v-model="form.deletion_mode" type="radio" value="delete_record_only" class="mt-1 text-tabarak-orange focus:ring-tabarak-orange" :disabled="form.processing" />
                        <FileX2 class="mt-0.5 size-5 shrink-0 text-tabarak-orange" />
                        <span><strong class="block text-sm text-tabarak-ink">Delete order record only</strong><span class="mt-1 block text-xs leading-5 text-slate-600">Use this when the order was fulfilled or the invoice record alone should be removed. Reserved stock stays deducted.</span></span>
                    </label>
                    <p v-if="form.errors.deletion_mode" class="text-sm font-semibold text-red-600">{{ form.errors.deletion_mode }}</p>
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-tabarak-line bg-tabarak-mist px-5 py-4 sm:flex-row sm:justify-end">
                    <button type="button" class="btn-secondary" :disabled="form.processing" @click="close">Keep order</button>
                    <button type="button" class="btn-danger" :disabled="form.processing" @click="submit"><Trash2 class="size-4" /> {{ form.processing ? 'Deleting…' : 'Delete and record notice' }}</button>
                </div>
            </section>
        </div>
    </Teleport>
</template>
