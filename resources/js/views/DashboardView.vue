<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../api';
import { liters, money } from '../format';

const loading = ref(true);
const error = ref('');
const data = ref(null);

async function load() {
    loading.value = true;
    error.value = '';

    try {
        data.value = (await api('/dashboard')).data;
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
}

onMounted(load);
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-end justify-between">
            <div>
                <h2 class="hidden text-2xl font-semibold md:block">Dashboard</h2>
                <p class="text-sm text-slate-400">{{ data?.date }}</p>
            </div>
            <button class="text-sm text-amber-400" type="button" @click="load">Refresh</button>
        </div>

        <p v-if="error" class="rounded-xl bg-red-500/15 px-3 py-2 text-sm text-red-300">{{ error }}</p>
        <p v-else-if="loading" class="text-slate-400">Loading today’s figures…</p>

        <template v-else>
            <div v-if="data.alerts?.length" class="space-y-2">
                <div
                    v-for="alert in data.alerts"
                    :key="alert.tank_id + alert.type"
                    class="rounded-2xl border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-sm text-amber-100"
                >
                    {{ alert.message }}
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <article class="rounded-2xl bg-slate-900 p-4">
                    <p class="text-xs text-slate-400">Today sales</p>
                    <p class="mt-1 text-xl font-semibold">{{ money(data.today_sales_amount) }}</p>
                </article>
                <article class="rounded-2xl bg-slate-900 p-4">
                    <p class="text-xs text-slate-400">Liters sold</p>
                    <p class="mt-1 text-xl font-semibold">{{ liters(data.today_liters) }}</p>
                </article>
                <article class="rounded-2xl bg-slate-900 p-4">
                    <p class="text-xs text-slate-400">Profit today</p>
                    <p class="mt-1 text-xl font-semibold text-emerald-400">{{ money(data.profit_today) }}</p>
                </article>
                <article class="rounded-2xl bg-slate-900 p-4">
                    <p class="text-xs text-slate-400">Credit outstanding</p>
                    <p class="mt-1 text-xl font-semibold">{{ money(data.credit_outstanding) }}</p>
                </article>
            </div>

            <section class="rounded-2xl bg-slate-900 p-4">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="font-semibold">Tank levels</h3>
                    <router-link class="text-sm text-amber-400" :to="{ name: 'tanks' }">Manage</router-link>
                </div>
                <div v-for="tank in data.tank_levels" :key="tank.id" class="mb-4 last:mb-0">
                    <div class="mb-1 flex justify-between text-sm">
                        <span>{{ tank.name }}</span>
                        <span :class="tank.is_low ? 'text-amber-400' : 'text-slate-400'">
                            {{ liters(tank.current_stock) }} / {{ liters(tank.capacity) }} L
                        </span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-800">
                        <div
                            class="h-full rounded-full"
                            :class="tank.is_low ? 'bg-amber-400' : 'bg-emerald-500'"
                            :style="{ width: `${Math.min(Number(tank.fill_percentage), 100)}%` }"
                        />
                    </div>
                </div>
            </section>

            <section class="rounded-2xl bg-slate-900 p-4 text-sm">
                <h3 class="mb-2 font-semibold">Shift</h3>
                <p v-if="data.open_shift" class="text-emerald-400">
                    Shift #{{ data.open_shift.id }} is open.
                </p>
                <p v-else class="text-slate-400">No open shift.</p>
                <router-link class="mt-3 inline-block text-amber-400" :to="{ name: 'shift' }">
                    Open shift desk →
                </router-link>
            </section>
        </template>
    </div>
</template>
