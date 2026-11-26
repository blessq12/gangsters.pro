<script setup>
import { ref } from "vue";
import { RouterLink, useRouter, useRoute } from "vue-router";
import { useAuthStore } from "../store/authStore";
import { useSessionStore } from "../store/sessionStore";
import { isRegisteredRole } from "../roles/registry";
import UiButton from "../components/ui/UiButton.vue";
import UiInput from "../components/ui/UiInput.vue";

const auth = useAuthStore();
const session = useSessionStore();
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
        const role = employee?.role;
        if (!isRegisteredRole(role)) {
            throw new Error("Неизвестная роль сотрудника.");
        }
        const module = await session.activateRole(role, router);
        const redirect = typeof route.query.redirect === "string" ? route.query.redirect : null;
        if (redirect && redirect.startsWith("/") && router.resolve(redirect).name) {
            await router.replace(redirect);
            return;
        }
        await router.replace({ name: module.home });
    } catch (reason) {
        error.value = reason instanceof Error ? reason.message : "Не удалось войти.";
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <section
        class="w-full max-w-sm border border-bo-border bg-bo-glass p-6 shadow-bo-lg backdrop-blur-md sm:p-8"
    >
        <h1 class="text-xl font-semibold text-bo-fg">Вход в бэкофис</h1>
        <p class="mt-1 text-sm text-bo-muted">Только для сотрудников.</p>
        <p
            v-if="error"
            class="mt-4 border border-red-500/40 bg-red-950/40 px-3 py-2 text-sm text-red-200"
        >
            {{ error }}
        </p>
        <form class="mt-6 space-y-4" @submit.prevent="submit">
            <label class="block text-sm">
                <span class="text-bo-muted">Email</span>
                <UiInput v-model="email" type="email" required autocomplete="username" />
            </label>
            <label class="block text-sm">
                <span class="text-bo-muted">Пароль</span>
                <UiInput
                    v-model="password"
                    type="password"
                    required
                    autocomplete="current-password"
                />
            </label>
            <UiButton type="submit" class="w-full justify-center" :disabled="submitting">
                Войти
            </UiButton>
        </form>
        <p class="mt-4 text-sm">
            <RouterLink
                :to="{ name: 'backoffice-forgot-password' }"
                class="text-bo-muted underline transition-colors hover:text-bo-accent"
            >
                Забыли пароль?
            </RouterLink>
        </p>
    </section>
</template>
