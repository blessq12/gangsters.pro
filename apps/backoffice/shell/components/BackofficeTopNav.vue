<script setup>
import { RouterLink, useRouter } from "vue-router";
import { useAuthStore } from "../store/authStore";

const auth = useAuthStore();
const router = useRouter();

async function logout() {
    await auth.logout();
    await router.push({ name: "backoffice-login" });
}
</script>

<template>
    <header
        class="flex h-12 shrink-0 items-center gap-4 border-b border-neutral-200 bg-white px-4"
    >
        <RouterLink
            :to="{ name: 'backoffice-home' }"
            class="text-sm font-medium text-neutral-900"
        >
            Backoffice
        </RouterLink>
        <div class="ml-auto flex items-center gap-3 text-sm text-neutral-600">
            <span v-if="auth.employee">{{ auth.employee.name }}</span>
            <button
                v-if="auth.authRequired || auth.employee"
                type="button"
                class="underline"
                @click="logout"
            >
                Выйти
            </button>
            <RouterLink
                v-else
                :to="{ name: 'backoffice-login' }"
                class="underline"
            >
                Войти
            </RouterLink>
        </div>
    </header>
</template>
