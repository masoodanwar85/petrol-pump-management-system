<script setup>
import { onMounted, reactive, ref } from 'vue';
import { api } from '../api';
import { money } from '../format';

const customers = ref([]);
const error = ref('');
const showForm = ref(false);
const form = reactive({
    name: '',
    phone: '',
    vehicle_number: '',
    credit_limit: '',
});

async function load() {
    try {
        customers.value = (await api('/customers')).data;
    } catch (e) {
        error.value = e.message;
    }
}

async function submit() {
    error.value = '';

    try {
        await api('/customers', {
            method: 'POST',
            body: {
                ...form,
                credit_limit: Number(form.credit_limit),
            },
        });
        showForm.value = false;
        form.name = '';
        form.phone = '';
        form.vehicle_number = '';
        form.credit_limit = '';
        await load();
    } catch (e) {
        error.value = e.message;
    }
}

onMounted(load);
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-stone-900">Customers</h2>
            <button class="link-accent" type="button" @click="showForm = !showForm">
                {{ showForm ? 'Cancel' : 'Add' }}
            </button>
        </div>
        <p v-if="error" class="alert-error">{{ error }}</p>

        <form v-if="showForm" class="card space-y-3" @submit.prevent="submit">
            <div>
                <label>Name</label>
                <input v-model="form.name" required>
            </div>
            <div>
                <label>Phone</label>
                <input v-model="form.phone" required>
            </div>
            <div>
                <label>Vehicle number</label>
                <input v-model="form.vehicle_number">
            </div>
            <div>
                <label>Credit limit</label>
                <input v-model="form.credit_limit" type="number" step="0.01" min="0" required>
            </div>
            <button class="btn-primary w-full" type="submit">Save customer</button>
        </form>

        <router-link
            v-for="customer in customers"
            :key="customer.id"
            :to="{ name: 'customer-detail', params: { id: customer.id } }"
            class="card block"
        >
            <div class="flex justify-between">
                <div>
                    <p class="font-semibold text-stone-900">{{ customer.name }}</p>
                    <p class="muted">{{ customer.phone }} · {{ customer.vehicle_number || 'No vehicle' }}</p>
                </div>
                <p class="text-sm">{{ money(customer.outstanding) }}</p>
            </div>
        </router-link>
    </div>
</template>
