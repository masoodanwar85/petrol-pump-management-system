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
    <div class="relative flex min-h-dvh items-center justify-center overflow-hidden px-4">
        <div class="absolute inset-0 bg-teal-950" />
        <div class="absolute -left-16 top-10 h-56 w-56 rounded-full bg-amber-400/20 blur-3xl" />
        <div class="absolute -right-10 bottom-10 h-64 w-64 rounded-full bg-teal-400/20 blur-3xl" />

        <form class="relative w-full max-w-sm rounded-3xl bg-white p-7 shadow-2xl" @submit.prevent="submit">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-teal-800 text-lg font-bold text-amber-300">
                P
            </div>
            <p class="mt-5 text-[11px] uppercase tracking-[0.22em] text-teal-700">Petrol pump</p>
            <h1 class="mt-1 text-2xl font-semibold text-stone-900">Admin sign in</h1>
            <p class="muted mt-1">Same Sanctum API the mobile client uses.</p>

            <div class="mt-6">
                <label for="email">Email</label>
                <input id="email" v-model="email" type="email" autocomplete="username" required>
            </div>
            <div class="mt-4">
                <label for="password">Password</label>
                <input id="password" v-model="password" type="password" autocomplete="current-password" required>
            </div>

            <p v-if="error" class="alert-error mt-4">{{ error }}</p>

            <button class="btn-primary mt-6 w-full" :disabled="loading" type="submit">
                {{ loading ? 'Signing in…' : 'Sign in' }}
            </button>
        </form>
    </div>
</template>
