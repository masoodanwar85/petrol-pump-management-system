<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../api';
import { liters, money } from '../format';

const date = ref(new Date().toISOString().slice(0, 10));
const report = ref(null);
const profit = ref(null);
const error = ref('');

async function load() {
    error.value = '';

    try {
        const [daily, profitReport] = await Promise.all([
            api(`/reports/daily?date=${date.value}`),
            api(`/reports/profit?from=${date.value}&to=${date.value}`),
        ]);
        report.value = daily.data;
        profit.value = profitReport.data;
    } catch (e) {
        error.value = e.message;
    }
}

onMounted(load);
</script>

<template>
    <div class="space-y-4">
        <h2 class="hidden text-2xl font-semibold md:block">Reports</h2>
        <form class="flex gap-3" @submit.prevent="load">
            <input v-model="date" type="date">
            <button class="rounded-xl bg-amber-500 px-4 font-semibold text-slate-950" type="submit">Load</button>
        </form>
        <p v-if="error" class="rounded-xl bg-red-500/15 px-3 py-2 text-sm text-red-300">{{ error }}</p>

        <section v-if="report" class="space-y-3 rounded-2xl bg-slate-900 p-4">
            <h3 class="font-semibold">Daily {{ report.date }}</h3>
            <p>Fuel sales: {{ money(report.fuel.sales_amount) }}</p>
            <p>Liters: {{ liters(report.fuel.liters_sold) }}</p>
            <p>Expected cash: {{ money(report.expected_cash) }}</p>
            <p>Credit sales: {{ money(report.credit_sales_amount) }}</p>
            <p>Expenses: {{ money(report.expenses) }}</p>
            <p class="text-emerald-400">Net profit: {{ money(report.net_profit) }}</p>

            <div class="mt-4 border-t border-slate-800 pt-3">
                <h4 class="mb-2 font-medium">Combined by fuel type</h4>
                <div v-for="row in report.fuel.by_fuel_type" :key="row.fuel_type_id" class="flex justify-between py-1 text-sm">
                    <span>{{ row.fuel_type }} · {{ liters(row.liters_sold) }} L</span>
                    <span>{{ money(row.total_amount) }}</span>
                </div>
            </div>

            <div class="mt-4 border-t border-slate-800 pt-3">
                <h4 class="mb-2 font-medium">Isolated by nozzle</h4>
                <div v-for="row in report.fuel.by_nozzle" :key="row.nozzle_id" class="flex justify-between py-1 text-sm">
                    <span>{{ row.label }} · {{ liters(row.liters_sold) }} L</span>
                    <span>{{ money(row.total_amount) }}</span>
                </div>
            </div>
        </section>

        <section v-if="profit" class="space-y-2 rounded-2xl bg-slate-900 p-4">
            <h3 class="font-semibold">Profit split</h3>
            <p>Fuel (weighted): {{ money(profit.fuel_profit.profit_weighted_average) }}</p>
            <p>Fuel vs purchases: {{ money(profit.fuel_profit.profit_vs_purchases) }}</p>
            <p>Products: {{ money(profit.product_profit.profit) }}</p>
        </section>
    </div>
</template>
