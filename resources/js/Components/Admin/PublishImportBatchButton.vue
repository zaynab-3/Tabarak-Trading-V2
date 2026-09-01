<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Send } from '@lucide/vue';

const props = defineProps<{ batchId: number; count: number }>();
const form = useForm({ allows_open_quantity: false });

const publish = () => {
    if (!window.confirm(`Publish ${props.count} analyzed product(s) to the shop?`)) return;
    form.post(route('admin.imports.publish.store', props.batchId), { preserveScroll: true });
};
</script>

<template>
    <div class="flex flex-wrap items-center gap-2 border border-emerald-200 bg-emerald-50 px-3 py-2">
        <label class="flex items-center gap-2 text-xs font-bold text-emerald-900">
            <input v-model="form.allows_open_quantity" type="checkbox" class="rounded border-emerald-300 text-forest-800 focus:ring-forest-700" />
            Open quantity for all
        </label>
        <button class="btn-primary min-h-9 px-3 py-2 text-xs" type="button" :disabled="form.processing" @click="publish">
            <Send class="size-3.5" /> {{ form.processing ? 'Publishing…' : `Publish all ${count}` }}
        </button>
    </div>
</template>
