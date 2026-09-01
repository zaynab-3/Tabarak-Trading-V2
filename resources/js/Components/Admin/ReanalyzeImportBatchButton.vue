<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { LoaderCircle, Sparkles } from '@lucide/vue';

const props = defineProps<{ batchId: number }>();
const form = useForm({});
const submit = () => form.post(route('admin.imports.analysis.store', props.batchId), { preserveScroll: true });
</script>

<template>
    <button class="btn-primary" type="button" :disabled="form.processing" @click="submit">
        <LoaderCircle v-if="form.processing" class="size-4 animate-spin" />
        <Sparkles v-else class="size-4" />
        {{ form.processing ? 'Queueing…' : 'Run Gemini analysis' }}
    </button>
</template>
