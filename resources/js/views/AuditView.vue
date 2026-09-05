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
        <h2 class="page-title">Audit log</h2>
        <p v-if="error" class="alert-error">{{ error }}</p>
        <article v-for="log in logs" :key="log.id" class="card text-sm">
            <div class="flex justify-between">
                <p class="font-medium text-stone-900">{{ enumLabel(log.action) }} {{ log.auditable_type }} #{{ log.auditable_id }}</p>
            </div>
            <p class="muted">{{ log.user?.name || 'System' }} · {{ when(log.created_at) }}</p>
        </article>
    </div>
</template>
