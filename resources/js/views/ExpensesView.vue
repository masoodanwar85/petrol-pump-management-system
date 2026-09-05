<script setup>
import { onMounted, reactive, ref } from 'vue';
import { api } from '../api';
import { day, enumLabel, money } from '../format';

const expenses = ref([]);
const error = ref('');
const form = reactive({
    title: '',
    amount: '',
    date: new Date().toISOString().slice(0, 10),
    category: 'misc',
    notes: '',
});

async function load() {
    try {
        expenses.value = (await api('/expenses')).data;
    } catch (e) {
        error.value = e.message;
    }
}

async function submit() {
    error.value = '';

    try {
        await api('/expenses', {
            method: 'POST',
            body: {
                ...form,
                amount: Number(form.amount),
            },
        });
        form.title = '';
        form.amount = '';
        form.notes = '';
        await load();
    } catch (e) {
        error.value = e.message;
    }
}

onMounted(load);
</script>

<template>
    <div class="space-y-4">
        <h2 class="page-title">Expenses</h2>
        <p v-if="error" class="alert-error">{{ error }}</p>

        <form class="card space-y-3" @submit.prevent="submit">
            <div>
                <label>Title</label>
                <input v-model="form.title" required>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label>Amount</label>
                    <input v-model="form.amount" type="number" step="0.01" required>
                </div>
                <div>
                    <label>Category</label>
                    <select v-model="form.category">
                        <option value="salary">Salary</option>
                        <option value="utility">Utility</option>
                        <option value="misc">Misc</option>
                    </select>
                </div>
            </div>
            <div>
                <label>Date</label>
                <input v-model="form.date" type="date" required>
            </div>
            <button class="btn-primary w-full" type="submit">Add expense</button>
        </form>

        <article v-for="expense in expenses" :key="expense.id" class="card">
            <div class="flex justify-between">
                <p class="font-medium text-stone-900">{{ expense.title }}</p>
                <p class="font-medium">{{ money(expense.amount) }}</p>
            </div>
            <p class="muted">{{ day(expense.date) }} · {{ enumLabel(expense.category) }}</p>
        </article>
    </div>
</template>
