<script setup>
import { ref } from "vue";
import { RouterLink } from "vue-router";
import { useAuthStore } from "../store/authStore";
import UiButton from "../components/ui/UiButton.vue";
import UiInput from "../components/ui/UiInput.vue";

const auth = useAuthStore();
const email = ref("");
const message = ref("");
const error = ref("");
const submitting = ref(false);

async function submit() {
    error.value = "";
    message.value = "";
    submitting.value = true;
    try {
        const payload = await auth.requestPasswordReset(email.value.trim());
        message.value = payload.message ?? "Запрос принят.";
    } catch (reason) {
        error.value = reason instanceof Error ? reason.message : "Не удалось отправить запрос.";
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <section class="w-full max-w-sm border border-bo-border bg-bo-glass p-6 shadow-bo-lg backdrop-blur-md sm:p-8">
        <h1 class="text-xl font-semibold text-bo-fg">Восстановление пароля</h1>
        <p class="mt-1 text-sm text-bo-muted">Пока заглушка — письмо не отправляется.</p>
        <p
            v-if="error"
            class="mt-4 border border-red-500/40 bg-red-950/40 px-3 py-2 text-sm text-red-200"
        >
            {{ error }}
        </p>
        <p
            v-if="message"
            class="mt-4 border border-bo-border px-3 py-2 text-sm text-bo-fg"
        >
            {{ message }}
        </p>
        <form
            class="mt-6 space-y-4"
            @submit.prevent="submit"
        >
            <label class="block text-sm">
                <span class="text-bo-muted">Email</span>
                <UiInput
                    v-model="email"
                    type="email"
                    required
                />
            </label>
            <UiButton
                type="submit"
                class="w-full justify-center"
                :disabled="submitting"
            >
                Отправить
            </UiButton>
        </form>
        <p class="mt-4 text-sm">
            <RouterLink
                :to="{ name: 'backoffice-login' }"
                class="text-bo-muted underline transition-colors hover:text-bo-accent"
            >
                Назад ко входу
            </RouterLink>
        </p>
    </section>
</template>
