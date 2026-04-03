<script setup>
import { ref } from "vue";
import { useToast } from "vue-toastification";
import { useUserStore } from "../../stores/userStore";
import { useRuPhoneModel } from "../../composables/client/useRuPhoneModel";
import { mapApiError } from "../../utils/api/mapApiError";

const emit = defineEmits(["logged-in"]);

const userStore = useUserStore();
const toast = useToast();

/** @type {import('vue').Ref<'phone' | 'email'>} */
const loginBy = ref("phone");

const showForgot = ref(false);
const forgotEmail = ref("");
const forgotLoading = ref(false);
const forgotError = ref("");

const form = ref({
    phone: "",
    email: "",
    password: "",
});

const phone = useRuPhoneModel(form, "phone");

const loading = ref(false);
const error = ref("");

const tabClass = (mode) => [
    "rounded-full px-3 py-1 text-[11px] font-medium transition",
    loginBy.value === mode
        ? "bg-amber-400 text-black shadow-[0_0_14px_rgba(251,191,36,0.7)]"
        : "bg-white/5 text-slate-200 hover:bg-white/10",
];

async function submit() {
    error.value = "";

    if (!form.value.password) {
        error.value = "Введите пароль";
        return;
    }

    if (loginBy.value === "phone") {
        if (!form.value.phone) {
            error.value = "Введите номер телефона";
            return;
        }
    } else {
        const emailTrim = (form.value.email || "").trim();
        if (!emailTrim) {
            error.value = "Введите email";
            return;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailTrim)) {
            error.value = "Некорректный формат email";
            return;
        }
    }

    loading.value = true;

    try {
        const emailTrim = (form.value.email || "").trim();
        await userStore.loginClient({
            phone: loginBy.value === "phone" ? form.value.phone || null : null,
            email: loginBy.value === "email" ? emailTrim : null,
            password: form.value.password,
        });

        emit("logged-in");
    } catch (e) {
        console.error(e);
        error.value = mapApiError(
            e,
            "Не удалось выполнить вход. Проверьте данные и попробуйте ещё раз.",
        );
    } finally {
        loading.value = false;
    }
}

async function submitForgot() {
    forgotError.value = "";
    const emailTrim = (forgotEmail.value || "").trim();
    if (!emailTrim) {
        forgotError.value = "Введите email";
        return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailTrim)) {
        forgotError.value = "Некорректный формат email";
        return;
    }

    forgotLoading.value = true;
    try {
        await userStore.requestPasswordReset(emailTrim);
        toast.info(
            "Если такой аккаунт есть, мы отправили письмо со ссылкой для сброса пароля.",
        );
        showForgot.value = false;
        forgotEmail.value = "";
    } catch (e) {
        console.error(e);
        forgotError.value = mapApiError(
            e,
            "Не удалось отправить запрос. Попробуй позже.",
        );
    } finally {
        forgotLoading.value = false;
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4 text-slate-50">
        <h3 class="text-base font-semibold text-slate-50">Вход в аккаунт</h3>

        <p class="text-xs text-slate-400">
            Войди по телефону или по email — и пароль.
        </p>

        <div class="flex flex-wrap gap-2">
            <button
                type="button"
                :class="tabClass('phone')"
                @click="loginBy = 'phone'"
            >
                Телефон
            </button>
            <button
                type="button"
                :class="tabClass('email')"
                @click="loginBy = 'email'"
            >
                Email
            </button>
        </div>

        <div class="space-y-3">
            <div v-if="loginBy === 'phone'">
                <label class="block text-xs font-medium text-slate-300 mb-1">
                    Телефон
                </label>
                <input
                    v-model="phone"
                    v-maska
                    data-maska="+7 (###) ###-##-##"
                    type="tel"
                    placeholder="+7 (___) ___-__-__"
                    class="w-full rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-sm text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                />
            </div>

            <div v-else>
                <label class="block text-xs font-medium text-slate-300 mb-1">
                    Email
                </label>
                <input
                    v-model="form.email"
                    type="email"
                    autocomplete="username"
                    placeholder="you@example.com"
                    class="w-full rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-sm text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                />
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">
                    Пароль
                </label>
                <input
                    v-model="form.password"
                    type="password"
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="w-full rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-sm text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                />
            </div>
        </div>

        <p v-if="error" class="text-xs text-red-400">
            {{ error }}
        </p>

        <button
            type="submit"
            :disabled="loading"
            class="inline-flex w-full items-center justify-center rounded-xl bg-amber-400 px-4 py-2 text-sm font-semibold text-black shadow-[0_0_18px_rgba(251,191,36,0.75)] transition hover:bg-amber-300 disabled:opacity-60 disabled:shadow-none"
        >
            <span v-if="!loading">Войти</span>
            <span v-else>Входим…</span>
        </button>

        <div class="border-t border-white/10 pt-3 space-y-2">
            <button
                type="button"
                class="text-[11px] text-amber-400/90 hover:text-amber-300 underline-offset-2 hover:underline"
                @click="showForgot = !showForgot"
            >
                {{ showForgot ? "Скрыть" : "Забыли пароль?" }}
            </button>

            <div
                v-if="showForgot"
                class="rounded-xl border border-white/10 bg-black/30 p-3 space-y-2"
            >
                <p class="text-[11px] text-slate-400">
                    Укажи email аккаунта — пришлём ссылку для нового пароля.
                </p>
                <input
                    v-model="forgotEmail"
                    type="email"
                    autocomplete="email"
                    placeholder="you@example.com"
                    class="w-full rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-sm text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                />
                <p v-if="forgotError" class="text-[11px] text-red-400">
                    {{ forgotError }}
                </p>
                <button
                    type="button"
                    :disabled="forgotLoading"
                    class="inline-flex w-full items-center justify-center rounded-lg border border-amber-400/50 bg-transparent px-3 py-1.5 text-xs font-medium text-amber-300 hover:bg-amber-400/10 disabled:opacity-50"
                    @click="submitForgot"
                >
                    <span v-if="!forgotLoading">Отправить ссылку</span>
                    <span v-else>Отправляем…</span>
                </button>
            </div>
        </div>
    </form>
</template>
