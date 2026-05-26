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
import { useAppDesign } from "../../design/useAppDesign";

const emit = defineEmits(["registered"]);

const cli = useAppDesign().components.client;
const s = cli.shared;

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
    <form
        :class="s.formRoot"
        @submit.prevent="submit"
    >
        <h3 :class="s.headingH3">
            Регистрация
        </h3>

        <p :class="s.leadMuted">
            Создаём аккаунт, чтобы сохранять адреса и заказы.
        </p>

        <div :class="s.fieldStack">
            <div>
                <label :class="s.label">
                    Имя
                </label>
                <input
                    v-model="form.name"
                    type="text"
                    placeholder="Как к тебе обращаться?"
                    :class="s.input"
                />
            </div>

            <div>
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

            <div>
                <label :class="s.label">
                    Email
                </label>
                <input
                    v-model="form.email"
                    type="email"
                    autocomplete="email"
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
                    placeholder="минимум 6 символов"
                    :class="s.input"
                />
            </div>

            <div>
                <label :class="s.label">
                    Подтверждение пароля
                </label>
                <input
                    v-model="form.confirmPassword"
                    type="password"
                    placeholder="введите пароль ещё раз"
                    :class="s.input"
                />
            </div>

            <div class="space-y-1">
                <label :class="s.checkboxRow">
                    <AppCheckbox v-model="form.consent_personal_data" />
                    <span>Согласен на обработку персональных данных</span>
                </label>
                <label :class="s.checkboxRowMuted">
                    <AppCheckbox v-model="form.consent_marketing" />
                    <span>Получать новости и акции</span>
                </label>
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
            <span v-if="!loading">Зарегистрироваться</span>
            <span v-else>Создаём аккаунт…</span>
        </button>
    </form>
</template>
