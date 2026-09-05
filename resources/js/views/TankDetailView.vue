<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import { api } from '../api';
import { day, liters, money } from '../format';

const route = useRoute();
const tank = ref(null);
const transactions = ref([]);
const error = ref('');
const message = ref('');
const saving = ref(false);
const form = reactive({
    type: 'purchase',
    quantity_liters: '',
    cost_per_liter: '',
    date: new Date().toISOString().slice(0, 10),
    reference: '',
    notes: '',
});

async function load() {
    error.value = '';

    try {
        tank.value = (await api(`/tanks/${route.params.id}`)).data;
        transactions.value = (await api(`/tanks/${route.params.id}/transactions`)).data;
    } catch (e) {
        error.value = e.message;
    }
}

async function submit() {
    saving.value = true;
    error.value = '';
    message.value = '';

    try {
        const body = {
            type: form.type,
            quantity_liters: Number(form.quantity_liters),
            date: form.date,
            reference: form.reference || null,
            notes: form.notes || null,
        };

        if (form.type === 'purchase') {
            body.cost_per_liter = Number(form.cost_per_liter);
        }

        const result = await api(`/tanks/${route.params.id}/transactions`, { method: 'POST', body });
        message.value = result.message;
        form.quantity_liters = '';
        form.cost_per_liter = '';
        await load();
    } catch (e) {
        error.value = e.message;
    } finally {
        saving.value = false;
    }
}

onMounted(load);
</script>

<template>
    <div class="space-y-4">
        <router-link class="link-accent" :to="{ name: 'tanks' }">← Tanks</router-link>
        <p v-if="error" class="alert-error">{{ error }}</p>
        <p v-if="message" class="alert-ok">{{ message }}</p>

        <section v-if="tank" class="card">
            <h2 class="text-xl font-semibold text-stone-900">{{ tank.name }}</h2>
            <p class="muted">{{ liters(tank.current_stock) }} L on hand</p>
        </section>

        <form class="card space-y-3" @submit.prevent="submit">
            <h3 class="font-semibold text-stone-900">Record movement</h3>
            <div>
                <label>Type</label>
                <select v-model="form.type">
                    <option value="purchase">Tanker purchase</option>
                    <option value="wastage">Wastage</option>
                    <option value="adjustment">Adjustment</option>
                </select>
            </div>
            <div>
                <label>Quantity (liters)</label>
                <input v-model="form.quantity_liters" type="number" step="0.001" required>
            </div>
            <div v-if="form.type === 'purchase'">
                <label>Cost per liter</label>
                <input v-model="form.cost_per_liter" type="number" step="0.01" required>
            </div>
            <div>
                <label>Date</label>
                <input v-model="form.date" type="date" required>
            </div>
            <div>
                <label>Reference</label>
                <input v-model="form.reference" type="text">
            </div>
            <button class="btn-primary w-full" :disabled="saving" type="submit">
                Save transaction
            </button>
        </form>

        <section class="card">
            <h3 class="mb-3 font-semibold text-stone-900">History</h3>
            <div v-for="row in transactions" :key="row.id" class="row py-3 text-sm last:border-0">
                <div class="flex justify-between">
                    <span class="capitalize">{{ row.type }}</span>
                    <span>{{ liters(row.quantity_liters) }} L</span>
                </div>
                <p class="muted">{{ day(row.date) }} · {{ row.total_cost ? money(row.total_cost) : '—' }}</p>
            </div>
        </section>
    </div>
</template>
