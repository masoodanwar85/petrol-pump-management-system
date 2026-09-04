<script setup>
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { api } from '../api';
import { clearSession, getUser } from '../auth';

const route = useRoute();
const router = useRouter();
const user = computed(() => getUser());

const tabs = [
    { name: 'dashboard', label: 'Home', icon: '▣' },
    { name: 'shift', label: 'Shift', icon: '◷' },
    { name: 'tanks', label: 'Tanks', icon: '⛽' },
    { name: 'more', label: 'More', icon: '☰' },
];

async function logout() {
    try {
        await api('/auth/logout', { method: 'POST' });
    } catch {
        // Token may already be invalid.
    }

    clearSession();
    await router.push({ name: 'login' });
}
</script>

<template>
    <div class="mx-auto flex min-h-dvh max-w-5xl flex-col md:flex-row">
        <aside class="hidden w-60 shrink-0 border-r border-slate-800 bg-slate-950 md:flex md:flex-col">
            <div class="border-b border-slate-800 px-5 py-5">
                <p class="text-xs uppercase tracking-widest text-amber-400">PPMS</p>
                <h1 class="mt-1 text-lg font-semibold">Pump Admin</h1>
            </div>
            <nav class="flex flex-1 flex-col gap-1 p-3">
                <router-link
                    v-for="tab in tabs"
                    :key="tab.name"
                    :to="{ name: tab.name }"
                    class="rounded-xl px-3 py-3 text-sm font-medium text-slate-300 hover:bg-slate-900"
                    active-class="bg-amber-500/15 text-amber-300"
                >
                    {{ tab.label }}
                </router-link>
                <router-link
                    :to="{ name: 'reports' }"
                    class="rounded-xl px-3 py-3 text-sm font-medium text-slate-300 hover:bg-slate-900"
                    active-class="bg-amber-500/15 text-amber-300"
                >
                    Reports
                </router-link>
            </nav>
            <div class="border-t border-slate-800 p-4 text-sm">
                <p class="font-medium">{{ user?.name }}</p>
                <p class="text-slate-400">{{ user?.email }}</p>
                <button class="mt-3 text-amber-400" type="button" @click="logout">Sign out</button>
            </div>
        </aside>

        <div class="flex min-h-dvh flex-1 flex-col">
            <header class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-800 bg-slate-950/90 px-4 py-3 backdrop-blur md:hidden">
                <div>
                    <p class="text-[11px] uppercase tracking-widest text-amber-400">PPMS</p>
                    <h1 class="text-base font-semibold">{{ route.meta.title || 'Admin' }}</h1>
                </div>
                <button class="text-sm text-amber-400" type="button" @click="logout">Sign out</button>
            </header>

            <main class="flex-1 px-4 pb-24 pt-4 md:px-8 md:pb-8 md:pt-6">
                <slot />
            </main>

            <nav class="fixed inset-x-0 bottom-0 z-10 grid grid-cols-4 border-t border-slate-800 bg-slate-950/95 pb-[env(safe-area-inset-bottom)] md:hidden">
                <router-link
                    v-for="tab in tabs"
                    :key="tab.name"
                    :to="{ name: tab.name }"
                    class="flex flex-col items-center gap-1 py-3 text-[11px] font-medium text-slate-400"
                    active-class="text-amber-400"
                >
                    <span class="text-base">{{ tab.icon }}</span>
                    {{ tab.label }}
                </router-link>
            </nav>
        </div>
    </div>
</template>
