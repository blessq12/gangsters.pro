<script setup>
import { ref, watch } from "vue";
import { useUserStore } from "../../modules/client/store/userStore";
import { useFormFieldErrors } from "../../platform/useFormFieldErrors";
import { mapApiError } from "../../platform/mapApiError";
import { applyApiFieldErrors } from "../../platform/extractApiFieldErrors";
import { useAppDesign } from "../../design/useAppDesign";
import FormField from "../ui/FormField.vue";

const props = defineProps({
    passwordResetNotice: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits([
    "logged-in",
    "go-register",
    "go-forgot",
    "clear-password-reset-notice",
]);

const cli = useAppDesign().components.client;
const s = cli.shared;

const userStore = useUserStore();
const fieldErrors = useFormFieldErrors();

const form = ref({
    email: "",
    password: "",
});

const loading = ref(false);

watch(
    () => form.value.email,
    () => fieldErrors.clearField("email"),
);
watch(
    () => form.value.password,
    () => fieldErrors.clearField("password"),
);

async function submit() {
    fieldErrors.clearAll();
    if (props.passwordResetNotice) {
        emit("clear-password-reset-notice");
    }

    const emailTrim = (form.value.email || "").trim();
    if (!emailTrim) {
        fieldErrors.setFieldError("email", "Введите email");
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailTrim)) {
        fieldErrors.setFieldError("email", "Некорректный формат email");
    }

    if (!form.value.password) {
        fieldErrors.setFieldError("password", "Введите пароль");
    }

    if (fieldErrors.hasAny.value) {
        return;
    }

    loading.value = true;

    try {
        await userStore.loginClient({
            phone: null,
            email: emailTrim,
            password: form.value.password,
        });

        emit("logged-in");
    } catch (e) {
        console.error(e);
        if (!applyApiFieldErrors(fieldErrors, e)) {
            fieldErrors.setFormError(
                mapApiError(
                    e,
                    "Не удалось выполнить вход. Проверьте данные и попробуйте ещё раз.",
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
        <p
            v-if="passwordResetNotice"
            :class="s.passwordResetNotice"
            role="status"
        >
            Письмо отправлено — проверь почту и войди с новым паролем.
        </p>

        <div :class="s.fieldStack">
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
                        autocomplete="username"
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
                        autocomplete="current-password"
                        placeholder="••••••••"
                        :class="[s.input, invalid && invalidClass]"
                        :aria-invalid="ariaInvalid"
                        :aria-describedby="describedBy"
                    />
                </template>
            </FormField>
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
            <span v-if="!loading">Войти</span>
            <span v-else>Входим…</span>
        </button>

        <div :class="s.loginFooter">
            <button
                type="button"
                :class="s.loginFooterLink"
                @click="emit('go-forgot')"
            >
                Забыли пароль?
            </button>
            <span :class="s.loginFooterSep" aria-hidden="true">·</span>
            <button
                type="button"
                :class="s.loginFooterLink"
                @click="emit('go-register')"
            >
                Регистрация
            </button>
        </div>
    </form>
</template>
