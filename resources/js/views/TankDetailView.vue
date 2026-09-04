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
        <router-link class="text-sm text-amber-400" :to="{ name: 'tanks' }">← Tanks</router-link>
        <p v-if="error" class="rounded-xl bg-red-500/15 px-3 py-2 text-sm text-red-300">{{ error }}</p>
        <p v-if="message" class="rounded-xl bg-emerald-500/15 px-3 py-2 text-sm text-emerald-200">{{ message }}</p>

        <section v-if="tank" class="rounded-2xl bg-slate-900 p-4">
            <h2 class="text-xl font-semibold">{{ tank.name }}</h2>
            <p class="text-sm text-slate-400">{{ liters(tank.current_stock) }} L on hand</p>
        </section>

        <form class="space-y-3 rounded-2xl bg-slate-900 p-4" @submit.prevent="submit">
            <h3 class="font-semibold">Record movement</h3>
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
            <button class="w-full rounded-xl bg-amber-500 py-3 font-semibold text-slate-950" :disabled="saving" type="submit">
                Save transaction
            </button>
        </form>

        <section class="rounded-2xl bg-slate-900 p-4">
            <h3 class="mb-3 font-semibold">History</h3>
            <div v-for="row in transactions" :key="row.id" class="border-b border-slate-800 py-3 text-sm last:border-0">
                <div class="flex justify-between">
                    <span class="capitalize">{{ row.type }}</span>
                    <span>{{ liters(row.quantity_liters) }} L</span>
                </div>
                <p class="text-slate-400">{{ day(row.date) }} · {{ row.total_cost ? money(row.total_cost) : '—' }}</p>
            </div>
        </section>
    </div>
</template>
