<script setup>
import { ref } from "vue";
import { RouterLink, useRouter, useRoute } from "vue-router";
import { useAuthStore } from "../store/authStore";
import { homeRouteForRole } from "../authConfig";

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const email = ref("");
const password = ref("");
const error = ref("");
const submitting = ref(false);

async function submit() {
    error.value = "";
    submitting.value = true;
    try {
        const employee = await auth.login(email.value.trim(), password.value);
        const redirect = typeof route.query.redirect === "string" ? route.query.redirect : null;
        if (redirect && redirect.startsWith("/")) {
            await router.replace(redirect);
            return;
        }
        await router.replace(homeRouteForRole(employee?.role));
    } catch (reason) {
        error.value = reason instanceof Error ? reason.message : "Не удалось войти.";
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <section class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center px-4 py-10">
        <h1 class="text-xl font-medium">Вход в backoffice</h1>
        <p class="mt-2 text-sm text-neutral-500">Регистрации нет — доступ только для сотрудников.</p>
        <p
            v-if="error"
            class="mt-4 text-sm text-red-700"
        >
            {{ error }}
        </p>
        <form
            class="mt-6 space-y-4"
            @submit.prevent="submit"
        >
            <label class="block text-sm">
                <span class="text-neutral-600">Email</span>
                <input
                    v-model="email"
                    type="email"
                    required
                    autocomplete="username"
                    class="mt-1 w-full rounded border border-neutral-300 bg-white px-3 py-2"
                >
            </label>
            <label class="block text-sm">
                <span class="text-neutral-600">Пароль</span>
                <input
                    v-model="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    class="mt-1 w-full rounded border border-neutral-300 bg-white px-3 py-2"
                >
            </label>
            <button
                type="submit"
                class="inline-flex h-10 items-center rounded bg-neutral-900 px-4 text-sm text-white disabled:opacity-50"
                :disabled="submitting"
            >
                Войти
            </button>
        </form>
        <p class="mt-4 text-sm">
            <RouterLink
                :to="{ name: 'backoffice-forgot-password' }"
                class="text-neutral-600 underline"
            >
                Забыли пароль?
            </RouterLink>
        </p>
    </section>
</template>
