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
            throw new Error("Unknown employee role.");
        }
        const module = await session.activateRole(role, router);
        const redirect = typeof route.query.redirect === "string" ? route.query.redirect : null;
        if (redirect && redirect.startsWith("/") && router.resolve(redirect).name) {
            await router.replace(redirect);
            return;
        }
        await router.replace({ name: module.home });
    } catch (reason) {
        error.value = reason instanceof Error ? reason.message : "Login failed.";
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <section class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center px-4 py-10">
        <h1 class="text-xl font-medium">Backoffice login</h1>
        <p class="mt-2 text-sm text-slate-500">Staff only. Role modules load after login.</p>
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
                <span class="text-slate-600">Email</span>
                <UiInput
                    v-model="email"
                    type="email"
                    required
                    autocomplete="username"
                />
            </label>
            <label class="block text-sm">
                <span class="text-slate-600">Password</span>
                <UiInput
                    v-model="password"
                    type="password"
                    required
                    autocomplete="current-password"
                />
            </label>
            <UiButton
                type="submit"
                :disabled="submitting"
            >
                Sign in
            </UiButton>
        </form>
        <p class="mt-4 text-sm">
            <RouterLink
                :to="{ name: 'backoffice-forgot-password' }"
                class="text-slate-600 underline"
            >
                Forgot password?
            </RouterLink>
        </p>
    </section>
</template>
