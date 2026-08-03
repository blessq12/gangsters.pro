<script setup>
import { ref, watch } from "vue";
import { useToast } from "vue-toastification";
import { useUserStore } from "../../modules/client/store/userStore";
import { useFormFieldErrors } from "../../platform/useFormFieldErrors";
import { mapApiError } from "../../platform/mapApiError";
import { applyApiFieldErrors } from "../../platform/extractApiFieldErrors";
import { useAppDesign } from "../../design/useAppDesign";
import FormField from "../ui/FormField.vue";

const emit = defineEmits(["logged-in", "go-register"]);

const cli = useAppDesign().components.client;
const s = cli.shared;

const userStore = useUserStore();
const toast = useToast();
const fieldErrors = useFormFieldErrors();
const forgotFieldErrors = useFormFieldErrors();

const showForgot = ref(false);
const forgotEmail = ref("");
const forgotLoading = ref(false);

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
watch(forgotEmail, () => forgotFieldErrors.clearField("email"));

async function submit() {
    fieldErrors.clearAll();

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

async function submitForgot() {
    forgotFieldErrors.clearAll();

    const emailTrim = (forgotEmail.value || "").trim();
    if (!emailTrim) {
        forgotFieldErrors.setFieldError("email", "Введите email");
        return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailTrim)) {
        forgotFieldErrors.setFieldError("email", "Некорректный формат email");
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
        if (!applyApiFieldErrors(forgotFieldErrors, e)) {
            forgotFieldErrors.setFormError(
                mapApiError(
                    e,
                    "Не удалось отправить запрос. Попробуй позже.",
                ),
            );
        }
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
                @click="showForgot = !showForgot"
            >
                {{ showForgot ? "Скрыть" : "Забыли пароль?" }}
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

        <div
            v-if="showForgot"
            :class="s.forgotIsland"
        >
            <FormField
                error-size="xs"
                :error="forgotFieldErrors.get('email')"
            >
                <template #default="{ id, invalid, invalidClass, describedBy, ariaInvalid }">
                    <input
                        :id="id"
                        v-model="forgotEmail"
                        type="email"
                        autocomplete="email"
                        placeholder="Email для сброса пароля"
                        :class="[s.input, invalid && invalidClass]"
                        :aria-invalid="ariaInvalid"
                        :aria-describedby="describedBy"
                    />
                </template>
            </FormField>
            <p
                v-if="forgotFieldErrors.formError"
                :class="s.error11"
            >
                {{ forgotFieldErrors.formError }}
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
    </form>
</template>
