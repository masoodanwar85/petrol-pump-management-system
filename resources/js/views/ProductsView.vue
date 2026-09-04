<script setup>
import { onMounted, reactive, ref } from 'vue';
import { api } from '../api';
import { liters, money } from '../format';

const products = ref([]);
const error = ref('');
const message = ref('');
const showCreate = ref(false);
const selected = ref(null);
const createForm = reactive({
    name: '',
    sku: '',
    unit: 'pcs',
    purchase_price: '',
    selling_price: '',
    current_stock: '0',
});
const stockForm = reactive({ type: 'purchase', quantity: '', unit_cost: '' });
const saleForm = reactive({ quantity: '', unit_price: '' });

async function load() {
    try {
        products.value = (await api('/products')).data;
    } catch (e) {
        error.value = e.message;
    }
}

async function createProduct() {
    error.value = '';

    try {
        await api('/products', {
            method: 'POST',
            body: {
                ...createForm,
                purchase_price: Number(createForm.purchase_price),
                selling_price: Number(createForm.selling_price),
                current_stock: Number(createForm.current_stock || 0),
            },
        });
        showCreate.value = false;
        await load();
    } catch (e) {
        error.value = e.message;
    }
}

async function addStock() {
    if (!selected.value) {
        return;
    }

    error.value = '';
    message.value = '';

    try {
        const result = await api(`/products/${selected.value.id}/stock`, {
            method: 'POST',
            body: {
                type: stockForm.type,
                quantity: Number(stockForm.quantity),
                unit_cost: stockForm.unit_cost ? Number(stockForm.unit_cost) : undefined,
            },
        });
        message.value = result.message;
        stockForm.quantity = '';
        await load();
        selected.value = products.value.find((item) => item.id === selected.value.id) || selected.value;
    } catch (e) {
        error.value = e.message;
    }
}

async function sell() {
    if (!selected.value) {
        return;
    }

    error.value = '';
    message.value = '';

    try {
        const result = await api(`/products/${selected.value.id}/sales`, {
            method: 'POST',
            body: {
                quantity: Number(saleForm.quantity),
                unit_price: saleForm.unit_price ? Number(saleForm.unit_price) : undefined,
            },
        });
        message.value = result.message;
        saleForm.quantity = '';
        await load();
        selected.value = products.value.find((item) => item.id === selected.value.id) || selected.value;
    } catch (e) {
        error.value = e.message;
    }
}

onMounted(load);
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">Products</h2>
            <button class="text-sm text-amber-400" type="button" @click="showCreate = !showCreate">
                {{ showCreate ? 'Cancel' : 'Add' }}
            </button>
        </div>
        <p v-if="error" class="rounded-xl bg-red-500/15 px-3 py-2 text-sm text-red-300">{{ error }}</p>
        <p v-if="message" class="rounded-xl bg-emerald-500/15 px-3 py-2 text-sm text-emerald-200">{{ message }}</p>

        <form v-if="showCreate" class="space-y-3 rounded-2xl bg-slate-900 p-4" @submit.prevent="createProduct">
            <div>
                <label>Name</label>
                <input v-model="createForm.name" required>
            </div>
            <div>
                <label>SKU</label>
                <input v-model="createForm.sku" required>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label>Cost</label>
                    <input v-model="createForm.purchase_price" type="number" step="0.01" required>
                </div>
                <div>
                    <label>Sell price</label>
                    <input v-model="createForm.selling_price" type="number" step="0.01" required>
                </div>
            </div>
            <button class="w-full rounded-xl bg-amber-500 py-3 font-semibold text-slate-950" type="submit">Save product</button>
        </form>

        <button
            v-for="product in products"
            :key="product.id"
            class="w-full rounded-2xl bg-slate-900 p-4 text-left"
            type="button"
            @click="selected = product"
        >
            <div class="flex justify-between">
                <div>
                    <p class="font-semibold">{{ product.name }}</p>
                    <p class="text-sm text-slate-400">{{ product.sku }}</p>
                </div>
                <p class="text-sm">{{ liters(product.current_stock) }} {{ product.unit }}</p>
            </div>
            <p class="mt-1 text-sm text-slate-400">{{ money(product.selling_price) }}</p>
        </button>

        <section v-if="selected" class="space-y-3 rounded-2xl border border-slate-800 bg-slate-900 p-4">
            <h3 class="font-semibold">{{ selected.name }}</h3>
            <form class="space-y-3" @submit.prevent="addStock">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label>Stock type</label>
                        <select v-model="stockForm.type">
                            <option value="purchase">Purchase</option>
                            <option value="adjustment">Adjustment</option>
                        </select>
                    </div>
                    <div>
                        <label>Qty</label>
                        <input v-model="stockForm.quantity" type="number" step="0.001" required>
                    </div>
                </div>
                <button class="w-full rounded-xl bg-slate-800 py-3 font-semibold" type="submit">Add stock</button>
            </form>
            <form class="space-y-3" @submit.prevent="sell">
                <div>
                    <label>Sell qty</label>
                    <input v-model="saleForm.quantity" type="number" step="0.001" required>
                </div>
                <button class="w-full rounded-xl bg-emerald-500 py-3 font-semibold text-slate-950" type="submit">Record sale</button>
            </form>
        </section>
    </div>
</template>
