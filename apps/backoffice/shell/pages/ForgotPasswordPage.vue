<script setup>
import { ref } from "vue";
import { RouterLink } from "vue-router";
import { useAuthStore } from "../store/authStore";

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
        error.value = reason instanceof Error ? reason.message : "Не удалось отправить request.";
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <section class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center px-4 py-10">
        <h1 class="text-xl font-medium">Восстановление пароля</h1>
        <p class="mt-2 text-sm text-neutral-500">Пока заглушка — письмо не отправляется.</p>
        <p
            v-if="error"
            class="mt-4 text-sm text-red-700"
        >
            {{ error }}
        </p>
        <p
            v-if="message"
            class="mt-4 text-sm text-neutral-700"
        >
            {{ message }}
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
                    class="mt-1 w-full rounded border border-neutral-300 bg-white px-3 py-2"
                >
            </label>
            <button
                type="submit"
                class="inline-flex h-10 items-center rounded bg-neutral-900 px-4 text-sm text-white disabled:opacity-50"
                :disabled="submitting"
            >
                Отправить
            </button>
        </form>
        <p class="mt-4 text-sm">
            <RouterLink
                :to="{ name: 'backoffice-login' }"
                class="text-neutral-600 underline"
            >
                Назад ко входу
            </RouterLink>
        </p>
    </section>
</template>
