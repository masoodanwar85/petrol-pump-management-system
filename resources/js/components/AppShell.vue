<script setup>
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { api } from '../api';
import { clearSession, getUser } from '../auth';

const route = useRoute();
const router = useRouter();
const user = computed(() => getUser());

const tabs = [
    { name: 'dashboard', label: 'Home', icon: 'home' },
    { name: 'shift', label: 'Shift', icon: 'clock' },
    { name: 'tanks', label: 'Tanks', icon: 'tank' },
    { name: 'more', label: 'More', icon: 'more' },
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
        <aside class="hidden w-64 shrink-0 bg-teal-950 text-white md:flex md:flex-col">
            <div class="border-b border-white/10 px-5 py-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-400 text-lg font-bold text-teal-950">
                    P
                </div>
                <p class="mt-4 text-[11px] uppercase tracking-[0.2em] text-amber-300">PPMS</p>
                <h1 class="mt-1 text-lg font-semibold">Pump Admin</h1>
            </div>
            <nav class="flex flex-1 flex-col gap-1 p-3">
                <router-link
                    v-for="tab in tabs"
                    :key="tab.name"
                    :to="{ name: tab.name }"
                    class="nav-link"
                >
                    {{ tab.label }}
                </router-link>
                <router-link :to="{ name: 'reports' }" class="nav-link">
                    Reports
                </router-link>
            </nav>
            <div class="border-t border-white/10 p-4 text-sm">
                <p class="font-medium">{{ user?.name }}</p>
                <p class="text-teal-200/70">{{ user?.email }}</p>
                <button class="mt-3 text-amber-300" type="button" @click="logout">Sign out</button>
            </div>
        </aside>

        <div class="flex min-h-dvh flex-1 flex-col">
            <header class="sticky top-0 z-10 flex items-center justify-between border-b border-stone-200/80 bg-[#f4efe6]/90 px-4 py-3 backdrop-blur md:hidden">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal-800 text-sm font-bold text-amber-300">
                        P
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-[0.18em] text-teal-700">PPMS</p>
                        <h1 class="text-base font-semibold text-stone-900">{{ route.meta.title || 'Admin' }}</h1>
                    </div>
                </div>
                <button class="link-accent" type="button" @click="logout">Sign out</button>
            </header>

            <main class="flex-1 px-4 pb-24 pt-4 md:px-8 md:pb-8 md:pt-6">
                <slot />
            </main>

            <nav class="fixed inset-x-0 bottom-0 z-10 grid grid-cols-4 border-t border-stone-200 bg-white/95 pb-[env(safe-area-inset-bottom)] shadow-[0_-8px_24px_rgb(28_25_23_/_0.06)] md:hidden">
                <router-link
                    v-for="tab in tabs"
                    :key="tab.name"
                    :to="{ name: tab.name }"
                    class="flex flex-col items-center gap-1 py-3 text-[11px] font-medium text-stone-400"
                    active-class="text-teal-700"
                >
                    <span class="text-lg leading-none">
                        <template v-if="tab.icon === 'home'">⌂</template>
                        <template v-else-if="tab.icon === 'clock'">◷</template>
                        <template v-else-if="tab.icon === 'tank'">⛽</template>
                        <template v-else>☰</template>
                    </span>
                    {{ tab.label }}
                </router-link>
            </nav>
        </div>
    </div>
</template>
