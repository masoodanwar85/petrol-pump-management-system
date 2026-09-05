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
        <router-link class="link-accent" :to="{ name: 'customers' }">← Customers</router-link>
        <p v-if="error" class="alert-error">{{ error }}</p>
        <p v-if="message" class="alert-ok">{{ message }}</p>

        <section v-if="customer" class="card">
            <h2 class="text-xl font-semibold text-stone-900">{{ customer.name }}</h2>
            <p class="muted">Outstanding {{ money(customer.outstanding) }} / limit {{ money(customer.credit_limit) }}</p>
        </section>

        <form class="card space-y-3" @submit.prevent="recordSale">
            <h3 class="font-semibold text-stone-900">Credit sale</h3>
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
            <button class="btn-primary w-full" type="submit">Post credit sale</button>
        </form>

        <form class="card space-y-3" @submit.prevent="recordPayment">
            <h3 class="font-semibold text-stone-900">Payment</h3>
            <div>
                <label>Amount</label>
                <input v-model="payment.amount" type="number" step="0.01" min="0.01" required>
            </div>
            <button class="btn-success w-full" type="submit">Record payment</button>
        </form>

        <section class="card">
            <h3 class="mb-3 font-semibold text-stone-900">Ledger</h3>
            <div v-for="row in ledger" :key="row.id" class="row flex justify-between py-2 text-sm last:border-0">
                <span>{{ day(row.date) }} · {{ enumLabel(row.type) }}</span>
                <span>{{ money(row.amount) }}</span>
            </div>
        </section>
    </div>
</template>
