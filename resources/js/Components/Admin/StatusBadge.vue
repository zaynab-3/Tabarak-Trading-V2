<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{ status: string }>();
const classes = computed(() => ({
    published: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    active: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    completed: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    review: 'bg-[#FFF0E8] text-tabarak-orange ring-tabarak-orange/20',
    processing: 'bg-tabarak-mist text-tabarak-blue ring-tabarak-blue/20',
    pending: 'bg-slate-100 text-slate-600 ring-slate-500/20',
    draft: 'bg-slate-100 text-slate-600 ring-slate-500/20',
    archived: 'bg-red-50 text-red-700 ring-red-600/20',
    failed: 'bg-red-50 text-red-700 ring-red-600/20',
    rejected: 'bg-red-50 text-red-700 ring-red-600/20',
}[props.status] ?? 'bg-slate-100 text-slate-600 ring-slate-500/20'));
</script>

<template><span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold capitalize ring-1 ring-inset" :class="classes">{{ status }}</span></template>
