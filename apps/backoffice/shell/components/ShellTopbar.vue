<script setup>
import { RouterLink, useRouter } from "vue-router";
import { useAuthStore } from "../store/authStore";
import { useSessionStore } from "../store/sessionStore";

const auth = useAuthStore();
const session = useSessionStore();
const router = useRouter();

async function logout() {
    await auth.logout();
    session.clear();
    await router.push({ name: "backoffice-login" });
}
</script>

<template>
    <header class="flex h-12 shrink-0 items-center gap-4 border-b border-slate-200 bg-white px-4 text-slate-900">
        <span class="text-sm font-medium">Backoffice</span>
        <nav
            v-if="session.navItems.length > 0"
            class="flex items-center gap-1 overflow-x-auto"
            aria-label="Role navigation"
        >
            <RouterLink
                v-for="item in session.navItems"
                :key="item.routeName"
                :to="{ name: item.routeName }"
                class="inline-flex h-9 items-center rounded px-3 text-sm text-slate-600 hover:bg-slate-100 hover:text-slate-900"
                active-class="bg-slate-200 font-medium text-slate-900"
            >
                {{ item.label }}
            </RouterLink>
        </nav>
        <div class="ml-auto flex items-center gap-3 text-sm text-slate-600">
            <span v-if="auth.employee">{{ auth.employee.name }}</span>
            <button
                v-if="auth.authRequired || auth.employee"
                type="button"
                class="underline"
                @click="logout"
            >
                Logout
            </button>
            <RouterLink
                v-else
                :to="{ name: 'backoffice-login' }"
                class="underline"
            >
                Login
            </RouterLink>
        </div>
    </header>
</template>
