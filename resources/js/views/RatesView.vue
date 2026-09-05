<script setup>
import { onMounted, reactive, ref } from 'vue';
import { api } from '../api';
import { money, when } from '../format';

const rates = ref([]);
const fuelTypes = ref([]);
const error = ref('');
const message = ref('');
const form = reactive({
    fuel_type_id: '',
    rate: '',
    effective_from: new Date().toISOString().slice(0, 16),
    notes: '',
});

async function load() {
    try {
        const [ratePage, types] = await Promise.all([
            api('/fuel-rates'),
            api('/fuel-types'),
        ]);
        rates.value = ratePage.data;
        fuelTypes.value = types.data;
        if (!form.fuel_type_id && types.data[0]) {
            form.fuel_type_id = types.data[0].id;
        }
    } catch (e) {
        error.value = e.message;
    }
}

async function submit() {
    error.value = '';
    message.value = '';

    try {
        const result = await api('/fuel-rates', {
            method: 'POST',
            body: {
                fuel_type_id: Number(form.fuel_type_id),
                rate: Number(form.rate),
                effective_from: form.effective_from,
                notes: form.notes || null,
            },
        });
        message.value = result.message;
        form.rate = '';
        await load();
    } catch (e) {
        error.value = e.message;
    }
}

onMounted(load);
</script>

<template>
    <div class="space-y-4">
        <h2 class="page-title">Fuel rates</h2>
        <p v-if="error" class="alert-error">{{ error }}</p>
        <p v-if="message" class="alert-ok">{{ message }}</p>

        <form class="card space-y-3" @submit.prevent="submit">
            <h3 class="font-semibold text-stone-900">New rate</h3>
            <div>
                <label>Fuel type</label>
                <select v-model="form.fuel_type_id" required>
                    <option v-for="type in fuelTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                </select>
            </div>
            <div>
                <label>Rate</label>
                <input v-model="form.rate" type="number" step="0.01" min="0.01" required>
            </div>
            <div>
                <label>Effective from</label>
                <input v-model="form.effective_from" type="datetime-local" required>
            </div>
            <button class="btn-primary w-full" type="submit">Publish rate</button>
        </form>

        <section class="card">
            <article v-for="rate in rates" :key="rate.id" class="row py-3 last:border-0">
                <div class="flex justify-between">
                    <p class="font-medium text-stone-900">{{ rate.fuel_type?.name || `Fuel #${rate.fuel_type_id}` }}</p>
                    <p class="font-semibold">{{ money(rate.rate) }}</p>
                </div>
                <p class="muted">
                    {{ when(rate.effective_from) }}
                    <span v-if="rate.effective_to"> → {{ when(rate.effective_to) }}</span>
                    <span v-else> · open</span>
                </p>
            </article>
        </section>
    </div>
</template>
