<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../api';
import { enumLabel, when } from '../format';

const logs = ref([]);
const error = ref('');

onMounted(async () => {
    try {
        logs.value = (await api('/audit-logs')).data;
    } catch (e) {
        error.value = e.message;
    }
});
</script>

<template>
    <div class="space-y-4">
        <h2 class="hidden text-2xl font-semibold md:block">Audit log</h2>
        <p v-if="error" class="rounded-xl bg-red-500/15 px-3 py-2 text-sm text-red-300">{{ error }}</p>
        <article v-for="log in logs" :key="log.id" class="rounded-2xl bg-slate-900 p-4 text-sm">
            <div class="flex justify-between">
                <p class="font-medium">{{ enumLabel(log.action) }} {{ log.auditable_type }} #{{ log.auditable_id }}</p>
            </div>
            <p class="text-slate-400">{{ log.user?.name || 'System' }} · {{ when(log.created_at) }}</p>
        </article>
    </div>
</template>
