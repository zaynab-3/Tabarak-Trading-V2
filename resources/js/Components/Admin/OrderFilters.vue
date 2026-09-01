<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { RotateCcw, Search } from '@lucide/vue';
import { computed, reactive } from 'vue';
import SelectMenu, { type SelectOption } from '@/Components/Shared/SelectMenu.vue';
import type { OrderFilters } from '@/types/orders';

const props = defineProps<{ filters: OrderFilters; statuses: string[] }>();
const form = reactive({ search: props.filters.search || '', status: props.filters.status || '' });
const statusOptions = computed<SelectOption[]>(() => [
    { value: '', label: 'All statuses' },
    ...props.statuses.map((status) => ({ value: status, label: status.charAt(0).toUpperCase() + status.slice(1) })),
]);
const apply = () => router.get(route('admin.orders.index'), form, { preserveState: true, replace: true });
const reset = () => router.get(route('admin.orders.index'));
</script>

<template>
    <form class="surface grid gap-3 p-4 sm:grid-cols-[minmax(0,1fr)_190px_auto]" @submit.prevent="apply">
        <label><span class="field-label">Search orders</span><div class="relative"><Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" /><input v-model="form.search" class="field-input pl-9" placeholder="Order, shop, or +1 phone" /></div></label>
        <label><span class="field-label">Status</span><SelectMenu v-model="form.status" :options="statusOptions" aria-label="Filter orders by status" /></label>
        <div class="flex items-end gap-2"><button class="btn-primary flex-1" type="submit">Apply</button><button class="admin-icon-button" type="button" aria-label="Reset filters" @click="reset"><RotateCcw class="size-4" /></button></div>
    </form>
</template>
