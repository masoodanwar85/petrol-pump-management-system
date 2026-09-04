<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { api } from '../api';
import { setSession } from '../auth';

const router = useRouter();
const route = useRoute();
const email = ref('admin@pump.test');
const password = ref('password');
const error = ref('');
const loading = ref(false);

async function submit() {
    error.value = '';
    loading.value = true;

    try {
        const result = await api('/auth/login', {
            method: 'POST',
            body: {
                email: email.value,
                password: password.value,
                device_name: 'admin-web',
            },
        });

        setSession(result.data.token, result.data.user);
        await router.replace(route.query.redirect || { name: 'dashboard' });
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="flex min-h-dvh items-center justify-center px-4">
        <form class="w-full max-w-sm rounded-3xl border border-slate-800 bg-slate-900/70 p-6 shadow-2xl" @submit.prevent="submit">
            <p class="text-xs uppercase tracking-[0.2em] text-amber-400">Petrol pump</p>
            <h1 class="mt-2 text-2xl font-semibold">Admin sign in</h1>
            <p class="mt-1 text-sm text-slate-400">Uses the same Sanctum API as the mobile client.</p>

            <div class="mt-6">
                <label for="email">Email</label>
                <input id="email" v-model="email" type="email" autocomplete="username" required>
            </div>
            <div class="mt-4">
                <label for="password">Password</label>
                <input id="password" v-model="password" type="password" autocomplete="current-password" required>
            </div>

            <p v-if="error" class="mt-4 rounded-xl bg-red-500/15 px-3 py-2 text-sm text-red-300">{{ error }}</p>

            <button
                class="mt-6 w-full rounded-xl bg-amber-500 py-3 text-base font-semibold text-slate-950 disabled:opacity-60"
                :disabled="loading"
                type="submit"
            >
                {{ loading ? 'Signing in…' : 'Sign in' }}
            </button>
        </form>
    </div>
</template>
