<script setup>
import { computed, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useToast } from "vue-toastification";
import { useUserStore } from "../stores/userStore";
import { mapApiError } from "../utils/api/mapApiError";

const route = useRoute();
const router = useRouter();
const userStore = useUserStore();
const toast = useToast();

const token = computed(() => {
    const t = route.query.token;
    if (Array.isArray(t)) {
        return t[0] || "";
    }
    return typeof t === "string" ? t : "";
});

const password = ref("");
const confirmPassword = ref("");
const loading = ref(false);
const error = ref("");

async function submit() {
    error.value = "";

    if (!token.value) {
        error.value = "Нет токена в ссылке. Запроси письмо со сбросом ещё раз.";
        return;
    }
    if (!password.value || password.value.length < 6) {
        error.value = "Пароль не короче 6 символов";
        return;
    }
    if (password.value !== confirmPassword.value) {
        error.value = "Пароли не совпадают";
        return;
    }

    loading.value = true;
    try {
        await userStore.changePasswordWithToken({
            token: token.value,
            password: password.value,
        });
        userStore.clearAuth();
        toast.success("Пароль обновлён. Войди с новым паролём.");
        await router.replace({ name: "home" });
    } catch (e) {
        console.error(e);
        error.value = mapApiError(
            e,
            "Не удалось сменить пароль. Ссылка могла устареть — запроси новую.",
        );
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="mx-auto max-w-md py-8 text-slate-50">
        <h1 class="text-lg font-semibold text-slate-50 mb-1">
            Новый пароль
        </h1>
        <p class="text-xs text-slate-400 mb-6">
            Придумай пароль для входа в личный кабинет.
        </p>

        <form
            v-if="token"
            class="space-y-4 rounded-3xl border border-amber-400/20 bg-[rgba(0,0,0,0.35)] px-4 py-5"
            @submit.prevent="submit"
        >
            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">
                    Пароль
                </label>
                <input
                    v-model="password"
                    type="password"
                    autocomplete="new-password"
                    placeholder="минимум 6 символов"
                    class="w-full rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-sm text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                />
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">
                    Подтверждение
                </label>
                <input
                    v-model="confirmPassword"
                    type="password"
                    autocomplete="new-password"
                    placeholder="ещё раз"
                    class="w-full rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-sm text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                />
            </div>
            <p v-if="error" class="text-xs text-red-400">
                {{ error }}
            </p>
            <button
                type="submit"
                :disabled="loading"
                class="inline-flex w-full items-center justify-center rounded-xl bg-amber-400 px-4 py-2 text-sm font-semibold text-black shadow-[0_0_18px_rgba(251,191,36,0.75)] transition hover:bg-amber-300 disabled:opacity-60"
            >
                <span v-if="!loading">Сохранить пароль</span>
                <span v-else>Сохраняем…</span>
            </button>
        </form>

        <div
            v-else
            class="rounded-3xl border border-red-500/30 bg-black/30 px-4 py-5 text-sm text-slate-300"
        >
            <p class="mb-3">
                Ссылка неполная: нет токена. Открой ссылку из письма или запроси
                сброс пароля снова в форме входа.
            </p>
            <RouterLink
                :to="{ name: 'home' }"
                class="text-amber-400 text-sm font-medium hover:text-amber-300"
            >
                На главную
            </RouterLink>
        </div>
    </div>
</template>
