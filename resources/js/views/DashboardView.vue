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
                <h2 class="page-title">Dashboard</h2>
                <p class="muted">{{ data?.date }}</p>
            </div>
            <button class="link-accent" type="button" @click="load">Refresh</button>
        </div>

        <p v-if="error" class="alert-error">{{ error }}</p>
        <p v-else-if="loading" class="muted">Loading today’s figures…</p>

        <template v-else>
            <div v-if="data.alerts?.length" class="space-y-2">
                <div
                    v-for="alert in data.alerts"
                    :key="alert.tank_id + alert.type"
                    class="alert-warn"
                >
                    {{ alert.message }}
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <article class="card border-l-4 border-l-teal-600">
                    <p class="stat-label">Today sales</p>
                    <p class="stat-value">{{ money(data.today_sales_amount) }}</p>
                </article>
                <article class="card border-l-4 border-l-sky-500">
                    <p class="stat-label">Liters sold</p>
                    <p class="stat-value">{{ liters(data.today_liters) }}</p>
                </article>
                <article class="card border-l-4 border-l-emerald-500">
                    <p class="stat-label">Profit today</p>
                    <p class="stat-value text-emerald-700">{{ money(data.profit_today) }}</p>
                </article>
                <article class="card border-l-4 border-l-amber-500">
                    <p class="stat-label">Credit outstanding</p>
                    <p class="stat-value">{{ money(data.credit_outstanding) }}</p>
                </article>
            </div>

            <section class="card">
                <h3 class="mb-3 font-semibold text-stone-900">By fuel type</h3>
                <div
                    v-for="row in data.sales_by_fuel_type"
                    :key="row.fuel_type_id"
                    class="row mb-3 flex items-center justify-between pb-3 last:mb-0 last:border-0 last:pb-0"
                >
                    <div>
                        <p class="font-medium">{{ row.fuel_type }}</p>
                        <p class="muted">{{ liters(row.liters_sold) }} L</p>
                    </div>
                    <p class="font-semibold text-stone-900">{{ money(row.total_amount) }}</p>
                </div>
            </section>

            <section class="card">
                <h3 class="mb-3 font-semibold text-stone-900">By nozzle</h3>
                <div
                    v-for="row in data.sales_by_nozzle"
                    :key="row.nozzle_id"
                    class="row mb-3 flex items-center justify-between pb-3 last:mb-0 last:border-0 last:pb-0"
                >
                    <div>
                        <p class="font-medium">{{ row.label }}</p>
                        <p class="muted">{{ row.fuel_type }} · {{ liters(row.liters_sold) }} L</p>
                    </div>
                    <p class="font-semibold text-stone-900">{{ money(row.total_amount) }}</p>
                </div>
            </section>

            <section class="card">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="font-semibold text-stone-900">Tank levels</h3>
                    <router-link class="link-accent" :to="{ name: 'tanks' }">Manage</router-link>
                </div>
                <div v-for="tank in data.tank_levels" :key="tank.id" class="mb-4 last:mb-0">
                    <div class="mb-1 flex justify-between text-sm">
                        <span class="font-medium">{{ tank.name }}</span>
                        <span :class="tank.is_low ? 'font-medium text-amber-700' : 'text-stone-500'">
                            {{ liters(tank.current_stock) }} / {{ liters(tank.capacity) }} L
                        </span>
                    </div>
                    <div class="h-2.5 overflow-hidden rounded-full bg-stone-100">
                        <div
                            class="h-full rounded-full"
                            :class="tank.is_low ? 'bg-amber-500' : 'bg-teal-600'"
                            :style="{ width: `${Math.min(Number(tank.fill_percentage), 100)}%` }"
                        />
                    </div>
                </div>
            </section>

            <section class="card text-sm">
                <h3 class="mb-2 font-semibold text-stone-900">Shift</h3>
                <p v-if="data.open_shift" class="font-medium text-emerald-700">
                    Shift #{{ data.open_shift.id }} is open.
                </p>
                <p v-else class="muted">No open shift.</p>
                <router-link class="link-accent mt-3 inline-block" :to="{ name: 'shift' }">
                    Open shift desk →
                </router-link>
            </section>
        </template>
    </div>
</template>
