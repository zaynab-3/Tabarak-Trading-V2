<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Send } from '@lucide/vue';
import { ref } from 'vue';
import ConfirmDialog from '@/Components/Shared/ConfirmDialog.vue';

const props = defineProps<{ batchId: number; count: number }>();
const form = useForm({ allows_open_quantity: false });
const confirming = ref(false);

const publish = () => {
    confirming.value = false;
    form.post(route('admin.imports.publish.store', props.batchId), { preserveScroll: true });
};
</script>

<template>
    <div class="flex flex-wrap items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2">
        <label class="flex items-center gap-2 text-xs font-bold text-emerald-900">
            <input v-model="form.allows_open_quantity" type="checkbox" class="rounded border-emerald-300 text-tabarak-blue focus:ring-tabarak-blue" />
            Open quantity for all
        </label>
        <button class="btn-primary min-h-9 px-3 py-2 text-xs" type="button" :disabled="form.processing" @click="confirming = true">
            <Send class="size-3.5" /> {{ form.processing ? 'Publishing…' : `Publish all ${count}` }}
        </button>
        <ConfirmDialog
            :open="confirming"
            title="Publish analyzed products?"
            :description="`${count} analyzed product(s) will be added to the public shop using the reviewed details.`"
            confirm-label="Publish products"
            tone="warning"
            :processing="form.processing"
            @cancel="confirming = false"
            @confirm="publish"
        />
    </div>
</template>
