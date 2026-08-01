<script setup>
import { ref, watch } from "vue";
import { useUserStore } from "../../stores/userStore";
import { useRuPhoneModel } from "../../composables/client/useRuPhoneModel";
import { useFormFieldErrors } from "../../composables/forms/useFormFieldErrors";
import {
    RU_PHONE_MASKA_PATTERN,
    RU_PHONE_MASKA_TOKENS_ATTR,
    validateRuPhoneForSubmit,
} from "../../validation/ruPhone";
import { mapApiError } from "../../utils/api/mapApiError";
import { applyApiFieldErrors } from "../../utils/api/extractApiFieldErrors";
import { useAppDesign } from "../../design/useAppDesign";
import FormField from "../ui/FormField.vue";

const emit = defineEmits(["registered"]);

const cli = useAppDesign().components.client;
const s = cli.shared;

const userStore = useUserStore();
const fieldErrors = useFormFieldErrors();

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

watch(
    () => form.value.name,
    () => fieldErrors.clearField("name"),
);
watch(
    () => form.value.phone,
    () => fieldErrors.clearField("phone"),
);
watch(
    () => form.value.email,
    () => fieldErrors.clearField("email"),
);
watch(
    () => form.value.password,
    () => {
        fieldErrors.clearField("password");
        fieldErrors.clearField("confirmPassword");
    },
);
watch(
    () => form.value.confirmPassword,
    () => fieldErrors.clearField("confirmPassword"),
);
watch(
    () => form.value.consent_personal_data,
    () => fieldErrors.clearField("consent_personal_data"),
);

async function submit() {
    fieldErrors.clearAll();

    if (!form.value.name) {
        fieldErrors.setFieldError("name", "Введите имя");
    }

    const phoneCheck = validateRuPhoneForSubmit(form.value.phone);
    if (!phoneCheck.ok) {
        fieldErrors.setFieldError("phone", phoneCheck.message);
    }

    const emailTrim = (form.value.email || "").trim();
    if (!emailTrim) {
        fieldErrors.setFieldError("email", "Введите электронную почту");
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailTrim)) {
        fieldErrors.setFieldError("email", "Некорректный формат email");
    }

    if (!form.value.password) {
        fieldErrors.setFieldError("password", "Придумайте пароль");
    }

    if (form.value.password !== form.value.confirmPassword) {
        fieldErrors.setFieldError("confirmPassword", "Пароль и подтверждение не совпадают");
    }

    if (!form.value.consent_personal_data) {
        fieldErrors.setFieldError(
            "consent_personal_data",
            "Нужно согласиться на обработку персональных данных",
        );
    }

    if (fieldErrors.hasAny.value) {
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
        if (!applyApiFieldErrors(fieldErrors, e)) {
            fieldErrors.setFormError(
                mapApiError(
                    e,
                    "Не удалось завершить регистрацию. Попробуйте ещё раз.",
                ),
            );
        }
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
            <FormField
                label="Имя"
                error-size="xs"
                :error="fieldErrors.get('name')"
            >
                <template #default="{ id, invalid, invalidClass, describedBy, ariaInvalid }">
                    <input
                        :id="id"
                        v-model="form.name"
                        type="text"
                        placeholder="Как к тебе обращаться?"
                        :class="[s.input, invalid && invalidClass]"
                        :aria-invalid="ariaInvalid"
                        :aria-describedby="describedBy"
                    />
                </template>
            </FormField>

            <FormField
                label="Телефон"
                error-size="xs"
                :error="fieldErrors.get('phone')"
            >
                <template #default="{ id, invalid, invalidClass, describedBy, ariaInvalid }">
                    <input
                        :id="id"
                        v-model="phoneMask.masked"
                        v-maska="phoneMask"
                        :data-maska="RU_PHONE_MASKA_PATTERN"
                        :data-maska-tokens="RU_PHONE_MASKA_TOKENS_ATTR"
                        type="tel"
                        placeholder="+7 (___) ___-__-__"
                        :class="[s.input, invalid && invalidClass]"
                        :aria-invalid="ariaInvalid"
                        :aria-describedby="describedBy"
                    />
                </template>
            </FormField>

            <FormField
                label="Email"
                error-size="xs"
                :error="fieldErrors.get('email')"
            >
                <template #default="{ id, invalid, invalidClass, describedBy, ariaInvalid }">
                    <input
                        :id="id"
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        placeholder="you@example.com"
                        :class="[s.input, invalid && invalidClass]"
                        :aria-invalid="ariaInvalid"
                        :aria-describedby="describedBy"
                    />
                </template>
            </FormField>

            <FormField
                label="Пароль"
                error-size="xs"
                :error="fieldErrors.get('password')"
            >
                <template #default="{ id, invalid, invalidClass, describedBy, ariaInvalid }">
                    <input
                        :id="id"
                        v-model="form.password"
                        type="password"
                        placeholder="минимум 6 символов"
                        :class="[s.input, invalid && invalidClass]"
                        :aria-invalid="ariaInvalid"
                        :aria-describedby="describedBy"
                    />
                </template>
            </FormField>

            <FormField
                label="Подтверждение пароля"
                error-size="xs"
                :error="fieldErrors.get('confirmPassword')"
            >
                <template #default="{ id, invalid, invalidClass, describedBy, ariaInvalid }">
                    <input
                        :id="id"
                        v-model="form.confirmPassword"
                        type="password"
                        placeholder="введите пароль ещё раз"
                        :class="[s.input, invalid && invalidClass]"
                        :aria-invalid="ariaInvalid"
                        :aria-describedby="describedBy"
                    />
                </template>
            </FormField>

            <div class="space-y-1">
                <FormField
                    error-size="xs"
                    :error="fieldErrors.get('consent_personal_data')"
                >
                    <template #default>
                        <label :class="s.checkboxRow">
                            <AppCheckbox v-model="form.consent_personal_data" />
                            <span>Согласен на обработку персональных данных</span>
                        </label>
                    </template>
                </FormField>
                <label :class="s.checkboxRowMuted">
                    <AppCheckbox v-model="form.consent_marketing" />
                    <span>Получать новости и акции</span>
                </label>
            </div>
        </div>

        <p
            v-if="fieldErrors.formError"
            :class="s.errorXs"
        >
            {{ fieldErrors.formError }}
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
