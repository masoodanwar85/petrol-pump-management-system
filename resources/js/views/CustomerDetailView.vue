<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import { api } from '../api';
import { day, enumLabel, money } from '../format';

const route = useRoute();
const customer = ref(null);
const ledger = ref([]);
const fuelTypes = ref([]);
const error = ref('');
const message = ref('');
const sale = reactive({ fuel_type_id: '', liters: '' });
const payment = reactive({ amount: '' });

async function load() {
    try {
        const [profile, entries, types] = await Promise.all([
            api(`/customers/${route.params.id}`),
            api(`/customers/${route.params.id}/ledger`),
            api('/fuel-types'),
        ]);
        customer.value = profile.data;
        ledger.value = entries.data;
        fuelTypes.value = types.data;
        if (!sale.fuel_type_id && types.data[0]) {
            sale.fuel_type_id = types.data[0].id;
        }
    } catch (e) {
        error.value = e.message;
    }
}

async function recordSale() {
    error.value = '';
    message.value = '';

    try {
        const result = await api(`/customers/${route.params.id}/credit-sales`, {
            method: 'POST',
            body: {
                fuel_type_id: Number(sale.fuel_type_id),
                liters: Number(sale.liters),
            },
        });
        message.value = result.message;
        sale.liters = '';
        await load();
    } catch (e) {
        error.value = e.message;
    }
}

async function recordPayment() {
    error.value = '';
    message.value = '';

    try {
        const result = await api(`/customers/${route.params.id}/payments`, {
            method: 'POST',
            body: { amount: Number(payment.amount) },
        });
        message.value = result.message;
        payment.amount = '';
        await load();
    } catch (e) {
        error.value = e.message;
    }
}

onMounted(load);
</script>

<template>
    <div class="space-y-4">
        <router-link class="text-sm text-amber-400" :to="{ name: 'customers' }">← Customers</router-link>
        <p v-if="error" class="rounded-xl bg-red-500/15 px-3 py-2 text-sm text-red-300">{{ error }}</p>
        <p v-if="message" class="rounded-xl bg-emerald-500/15 px-3 py-2 text-sm text-emerald-200">{{ message }}</p>

        <section v-if="customer" class="rounded-2xl bg-slate-900 p-4">
            <h2 class="text-xl font-semibold">{{ customer.name }}</h2>
            <p class="text-sm text-slate-400">Outstanding {{ money(customer.outstanding) }} / limit {{ money(customer.credit_limit) }}</p>
        </section>

        <form class="space-y-3 rounded-2xl bg-slate-900 p-4" @submit.prevent="recordSale">
            <h3 class="font-semibold">Credit sale</h3>
            <div>
                <label>Fuel</label>
                <select v-model="sale.fuel_type_id">
                    <option v-for="type in fuelTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                </select>
            </div>
            <div>
                <label>Liters</label>
                <input v-model="sale.liters" type="number" step="0.001" min="0.001" required>
            </div>
            <button class="w-full rounded-xl bg-amber-500 py-3 font-semibold text-slate-950" type="submit">Post credit sale</button>
        </form>

        <form class="space-y-3 rounded-2xl bg-slate-900 p-4" @submit.prevent="recordPayment">
            <h3 class="font-semibold">Payment</h3>
            <div>
                <label>Amount</label>
                <input v-model="payment.amount" type="number" step="0.01" min="0.01" required>
            </div>
            <button class="w-full rounded-xl bg-emerald-500 py-3 font-semibold text-slate-950" type="submit">Record payment</button>
        </form>

        <section class="rounded-2xl bg-slate-900 p-4">
            <h3 class="mb-3 font-semibold">Ledger</h3>
            <div v-for="row in ledger" :key="row.id" class="flex justify-between border-b border-slate-800 py-2 text-sm last:border-0">
                <span>{{ day(row.date) }} · {{ enumLabel(row.type) }}</span>
                <span>{{ money(row.amount) }}</span>
            </div>
        </section>
    </div>
</template>
