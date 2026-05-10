<script setup>
import { ref } from "vue";
import { useToast } from "vue-toastification";
import { useUserStore } from "../../stores/userStore";
import { useRuPhoneModel } from "../../composables/client/useRuPhoneModel";
import {
    RU_PHONE_MASKA_PATTERN,
    RU_PHONE_MASKA_TOKENS_ATTR,
    validateRuPhoneForSubmit,
} from "../../validation/ruPhone";
import { mapApiError } from "../../utils/api/mapApiError";
import { useAppDesign } from "../../design/useAppDesign";

const emit = defineEmits(["logged-in"]);

const cli = useAppDesign().components.client;
const s = cli.shared;

const userStore = useUserStore();
const toast = useToast();

/** @type {import('vue').Ref<'phone' | 'email'>} */
const loginBy = ref("phone");

function tabClass(mode) {
    return [
        s.tabPillBase,
        loginBy.value === mode ? s.tabPillActive : s.tabPillInactive,
    ];
}

const showForgot = ref(false);
const forgotEmail = ref("");
const forgotLoading = ref(false);
const forgotError = ref("");

const form = ref({
    phone: "",
    email: "",
    password: "",
});

const { phoneMask } = useRuPhoneModel(form, "phone");

const loading = ref(false);
const error = ref("");

async function submit() {
    error.value = "";

    if (!form.value.password) {
        error.value = "Введите пароль";
        return;
    }

    if (loginBy.value === "phone") {
        const phoneCheck = validateRuPhoneForSubmit(form.value.phone);
        if (!phoneCheck.ok) {
            error.value = phoneCheck.message;
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
    <form
        :class="s.formRoot"
        @submit.prevent="submit"
    >
        <h3 :class="s.headingH3">
            Вход в аккаунт
        </h3>

        <p :class="s.leadMuted">
            Войди по телефону или по email — и пароль.
        </p>

        <div :class="s.tabRow">
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

        <div :class="s.fieldStack">
            <div v-if="loginBy === 'phone'">
                <label :class="s.label">
                    Телефон
                </label>
                <input
                    v-model="phoneMask.masked"
                    v-maska="phoneMask"
                    :data-maska="RU_PHONE_MASKA_PATTERN"
                    :data-maska-tokens="RU_PHONE_MASKA_TOKENS_ATTR"
                    type="tel"
                    placeholder="+7 (___) ___-__-__"
                    :class="s.input"
                />
            </div>

            <div v-else>
                <label :class="s.label">
                    Email
                </label>
                <input
                    v-model="form.email"
                    type="email"
                    autocomplete="username"
                    placeholder="you@example.com"
                    :class="s.input"
                />
            </div>

            <div>
                <label :class="s.label">
                    Пароль
                </label>
                <input
                    v-model="form.password"
                    type="password"
                    autocomplete="current-password"
                    placeholder="••••••••"
                    :class="s.input"
                />
            </div>
        </div>

        <p
            v-if="error"
            :class="s.errorXs"
        >
            {{ error }}
        </p>

        <button
            type="submit"
            :disabled="loading"
            :class="s.btnPrimaryWide"
        >
            <span v-if="!loading">Войти</span>
            <span v-else>Входим…</span>
        </button>

        <div :class="s.forgotSectionTop">
            <button
                type="button"
                :class="s.forgotToggle"
                @click="showForgot = !showForgot"
            >
                {{ showForgot ? "Скрыть" : "Забыли пароль?" }}
            </button>

            <div
                v-if="showForgot"
                :class="s.forgotIsland"
            >
                <p :class="s.forgotHint">
                    Укажи email аккаунта — пришлём ссылку для нового пароля.
                </p>
                <input
                    v-model="forgotEmail"
                    type="email"
                    autocomplete="email"
                    placeholder="you@example.com"
                    :class="s.input"
                />
                <p
                    v-if="forgotError"
                    :class="s.error11"
                >
                    {{ forgotError }}
                </p>
                <button
                    type="button"
                    :disabled="forgotLoading"
                    :class="s.forgotSubmitBtn"
                    @click="submitForgot"
                >
                    <span v-if="!forgotLoading">Отправить ссылку</span>
                    <span v-else>Отправляем…</span>
                </button>
            </div>
        </div>
    </form>
</template>
