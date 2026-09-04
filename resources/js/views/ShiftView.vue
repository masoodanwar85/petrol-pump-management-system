<script setup>
import { computed, onMounted, ref } from 'vue';
import { api } from '../api';
import { liters, money } from '../format';

const loading = ref(true);
const saving = ref(false);
const error = ref('');
const message = ref('');
const shift = ref(null);
const nozzles = ref([]);
const openings = ref({});
const closings = ref({});

const canStart = computed(() => !shift.value);
const canEnd = computed(() => Boolean(shift.value));

async function load() {
    loading.value = true;
    error.value = '';

    try {
        const [current, nozzleList] = await Promise.all([
            api('/shifts/current'),
            api('/nozzles'),
        ]);

        shift.value = current.data;
        nozzles.value = nozzleList.data;
        openings.value = Object.fromEntries(
            nozzleList.data.map((nozzle) => [
                nozzle.id,
                existingOpening(nozzle) ?? nozzle.last_closing_reading ?? '',
            ]),
        );
        closings.value = Object.fromEntries(
            nozzleList.data.map((nozzle) => [nozzle.id, existingClosing(nozzle) ?? '']),
        );
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
}

function existingOpening(nozzle) {
    return shift.value?.meter_readings?.find((row) => row.nozzle_id === nozzle.id)?.opening_reading;
}

function existingClosing(nozzle) {
    return shift.value?.meter_readings?.find((row) => row.nozzle_id === nozzle.id)?.closing_reading;
}

async function startShift() {
    saving.value = true;
    error.value = '';
    message.value = '';

    try {
        const result = await api('/shifts/start', {
            method: 'POST',
            body: {
                readings: nozzles.value.map((nozzle) => ({
                    nozzle_id: nozzle.id,
                    opening_reading: Number(openings.value[nozzle.id]),
                })),
            },
        });
        shift.value = result.data;
        message.value = result.message;
    } catch (e) {
        error.value = e.message;
    } finally {
        saving.value = false;
    }
}

async function endShift() {
    if (!shift.value) {
        return;
    }

    saving.value = true;
    error.value = '';
    message.value = '';

    try {
        const result = await api(`/shifts/${shift.value.id}/end`, {
            method: 'POST',
            body: {
                readings: nozzles.value.map((nozzle) => ({
                    nozzle_id: nozzle.id,
                    closing_reading: Number(closings.value[nozzle.id]),
                })),
            },
        });
        shift.value = result.data;
        message.value = result.message;
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
        <h2 class="hidden text-2xl font-semibold md:block">Shift desk</h2>
        <p v-if="error" class="rounded-xl bg-red-500/15 px-3 py-2 text-sm text-red-300">{{ error }}</p>
        <p v-if="message" class="rounded-xl bg-emerald-500/15 px-3 py-2 text-sm text-emerald-200">{{ message }}</p>
        <p v-if="loading" class="text-slate-400">Loading nozzles…</p>

        <section v-else class="rounded-2xl bg-slate-900 p-4">
            <p class="text-sm text-slate-400">
                <span v-if="shift">Shift #{{ shift.id }} is {{ shift.status }}.</span>
                <span v-else>No open shift. Enter opening readings for every nozzle.</span>
            </p>

            <div class="mt-4 space-y-3">
                <article v-for="nozzle in nozzles" :key="nozzle.id" class="rounded-xl border border-slate-800 p-3">
                    <p class="font-medium">{{ nozzle.label }}</p>
                    <p class="text-xs text-slate-400">{{ nozzle.fuel_type?.name || nozzle.fuel_type?.code }}</p>
                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <div>
                            <label>Opening</label>
                            <input v-model="openings[nozzle.id]" type="number" step="0.001" min="0" :disabled="!canStart">
                        </div>
                        <div>
                            <label>Closing</label>
                            <input v-model="closings[nozzle.id]" type="number" step="0.001" min="0" :disabled="!canEnd">
                        </div>
                    </div>
                </article>
            </div>

            <button
                v-if="canStart"
                class="mt-4 w-full rounded-xl bg-amber-500 py-3 font-semibold text-slate-950 disabled:opacity-60"
                :disabled="saving"
                type="button"
                @click="startShift"
            >
                Start shift
            </button>
            <button
                v-else-if="canEnd && shift.status === 'open'"
                class="mt-4 w-full rounded-xl bg-emerald-500 py-3 font-semibold text-slate-950 disabled:opacity-60"
                :disabled="saving"
                type="button"
                @click="endShift"
            >
                End shift and post sales
            </button>
        </section>

        <section v-if="shift?.sales?.length" class="rounded-2xl bg-slate-900 p-4">
            <h3 class="mb-3 font-semibold">Derived sales</h3>
            <div v-for="sale in shift.sales" :key="sale.id" class="flex justify-between border-b border-slate-800 py-2 text-sm last:border-0">
                <span>{{ liters(sale.liters_sold) }} L</span>
                <span>{{ money(sale.total_amount) }}</span>
            </div>
            <p v-if="shift.expected_cash" class="mt-3 text-sm text-slate-300">
                Expected cash: {{ money(shift.expected_cash) }}
            </p>
        </section>
    </div>
</template>
