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
        <h2 class="hidden text-2xl font-semibold md:block">Tanks</h2>
        <p v-if="error" class="rounded-xl bg-red-500/15 px-3 py-2 text-sm text-red-300">{{ error }}</p>
        <router-link
            v-for="tank in tanks"
            :key="tank.id"
            :to="{ name: 'tank-detail', params: { id: tank.id } }"
            class="block rounded-2xl bg-slate-900 p-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-semibold">{{ tank.name }}</p>
                    <p class="text-sm text-slate-400">{{ tank.fuel_type?.name }}</p>
                </div>
                <span v-if="tank.is_low" class="rounded-full bg-amber-500/20 px-2 py-1 text-xs text-amber-300">Low</span>
            </div>
            <p class="mt-3 text-sm text-slate-300">
                {{ liters(tank.current_stock) }} / {{ liters(tank.capacity) }} L
            </p>
            <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-800">
                <div
                    class="h-full rounded-full"
                    :class="tank.is_low ? 'bg-amber-400' : 'bg-emerald-500'"
                    :style="{ width: `${Math.min(Number(tank.fill_percentage), 100)}%` }"
                />
            </div>
        </router-link>
    </div>
</template>
