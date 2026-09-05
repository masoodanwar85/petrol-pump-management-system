<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../api';
import { liters } from '../format';

const tanks = ref([]);
const error = ref('');

onMounted(async () => {
    try {
        tanks.value = (await api('/tanks')).data;
    } catch (e) {
        error.value = e.message;
    }
});
</script>

<template>
    <div class="space-y-4">
        <h2 class="page-title">Tanks</h2>
        <p v-if="error" class="alert-error">{{ error }}</p>
        <router-link
            v-for="tank in tanks"
            :key="tank.id"
            :to="{ name: 'tank-detail', params: { id: tank.id } }"
            class="card block"
        >
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-semibold text-stone-900">{{ tank.name }}</p>
                    <p class="muted">{{ tank.fuel_type?.name }}</p>
                </div>
                <span v-if="tank.is_low" class="rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-800">Low</span>
            </div>
            <p class="mt-3 text-sm text-stone-600">
                {{ liters(tank.current_stock) }} / {{ liters(tank.capacity) }} L
            </p>
            <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-stone-100">
                <div
                    class="h-full rounded-full"
                    :class="tank.is_low ? 'bg-amber-500' : 'bg-teal-600'"
                    :style="{ width: `${Math.min(Number(tank.fill_percentage), 100)}%` }"
                />
            </div>
        </router-link>
    </div>
</template>
