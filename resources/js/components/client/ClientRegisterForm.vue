<script setup>
import { ref } from "vue";
import { useUserStore } from "../../stores/userStore";
import { useRuPhoneModel } from "../../composables/client/useRuPhoneModel";
import {
    RU_PHONE_MASKA_PATTERN,
    RU_PHONE_MASKA_TOKENS_ATTR,
    validateRuPhoneForSubmit,
} from "../../validation/ruPhone";
import { mapApiError } from "../../utils/api/mapApiError";

const emit = defineEmits(["registered"]);

const userStore = useUserStore();

const form = ref({
    name: "",
    phone: "",
    email: "",
    password: "",
    confirmPassword: "",
    consent_personal_data: true,
    consent_marketing: false,
});

const { phoneMask } = useRuPhoneModel(form, "phone");

const loading = ref(false);
const error = ref("");

async function submit() {
    error.value = "";

    if (!form.value.name) {
        error.value = "Введите имя";
        return;
    }
    const phoneCheck = validateRuPhoneForSubmit(form.value.phone);
    if (!phoneCheck.ok) {
        error.value = phoneCheck.message;
        return;
    }
    const emailTrim = (form.value.email || "").trim();
    if (!emailTrim) {
        error.value = "Введите электронную почту";
        return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailTrim)) {
        error.value = "Некорректный формат email";
        return;
    }
    if (!form.value.password) {
        error.value = "Придумайте пароль";
        return;
    }
    if (form.value.password !== form.value.confirmPassword) {
        error.value = "Пароль и подтверждение не совпадают";
        return;
    }
    if (!form.value.consent_personal_data) {
        error.value = "Нужно согласиться на обработку персональных данных";
        return;
    }

    loading.value = true;

    try {
        await userStore.registerClient({
            name: form.value.name,
            phone: form.value.phone,
            email: emailTrim,
            password: form.value.password,
            consent_personal_data: form.value.consent_personal_data,
            consent_marketing: form.value.consent_marketing,
        });

        emit("registered");
    } catch (e) {
        console.error(e);
        error.value = mapApiError(
            e,
            "Не удалось завершить регистрацию. Попробуйте ещё раз.",
        );
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4 text-slate-50">
        <h3 class="text-base font-semibold text-slate-50">Регистрация</h3>

        <p class="text-xs text-slate-400">
            Создаём аккаунт, чтобы сохранять адреса и заказы.
        </p>

        <div class="space-y-3">
            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">
                    Имя
                </label>
                <input
                    v-model="form.name"
                    type="text"
                    placeholder="Как к тебе обращаться?"
                    class="w-full rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-sm text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                />
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">
                    Телефон
                </label>
                <input
                    v-model="phoneMask.masked"
                    v-maska="phoneMask"
                    :data-maska="RU_PHONE_MASKA_PATTERN"
                    :data-maska-tokens="RU_PHONE_MASKA_TOKENS_ATTR"
                    type="tel"
                    placeholder="+7 (___) ___-__-__"
                    class="w-full rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-sm text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                />
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">
                    Email
                </label>
                <input
                    v-model="form.email"
                    type="email"
                    autocomplete="email"
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
                    placeholder="минимум 6 символов"
                    class="w-full rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-sm text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                />
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">
                    Подтверждение пароля
                </label>
                <input
                    v-model="form.confirmPassword"
                    type="password"
                    placeholder="введите пароль ещё раз"
                    class="w-full rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-sm text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                />
            </div>

            <div class="space-y-1">
                <label class="flex items-center gap-2 text-xs text-slate-300">
                    <input
                        v-model="form.consent_personal_data"
                        type="checkbox"
                        class="h-4 w-4 rounded border-white/20 bg-black/60 text-amber-400 focus:ring-amber-400/60"
                    />
                    <span>Согласен на обработку персональных данных</span>
                </label>
                <label class="flex items-center gap-2 text-xs text-slate-400">
                    <input
                        v-model="form.consent_marketing"
                        type="checkbox"
                        class="h-4 w-4 rounded border-white/20 bg-black/60 text-amber-400 focus:ring-amber-400/60"
                    />
                    <span>Получать новости и акции</span>
                </label>
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
            <span v-if="!loading">Зарегистрироваться</span>
            <span v-else>Создаём аккаунт…</span>
        </button>
    </form>
</template>
