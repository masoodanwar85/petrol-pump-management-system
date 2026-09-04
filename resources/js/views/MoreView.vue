<script setup>
const links = [
    { name: 'customers', label: 'Credit customers', hint: 'Ledger, limits, payments' },
    { name: 'products', label: 'Shop products', hint: 'Oil, stock, counter sales' },
    { name: 'expenses', label: 'Expenses', hint: 'Salary, utility, misc' },
    { name: 'rates', label: 'Fuel rates', hint: 'Publish a new price window' },
    { name: 'reports', label: 'Reports', hint: 'Daily sales and profit' },
    { name: 'audit', label: 'Audit log', hint: 'Who changed what' },
];
</script>

<template>
    <div class="space-y-3">
        <h2 class="hidden text-2xl font-semibold md:block">More</h2>
        <router-link
            v-for="link in links"
            :key="link.name"
            :to="{ name: link.name }"
            class="block rounded-2xl bg-slate-900 p-4"
        >
            <p class="font-semibold">{{ link.label }}</p>
            <p class="text-sm text-slate-400">{{ link.hint }}</p>
        </router-link>
    </div>
</template>
