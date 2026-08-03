<script setup>
import { ref, watch } from "vue";
import { useToast } from "vue-toastification";
import { useUserStore } from "../../modules/client/store/userStore";
import { useRuPhoneModel } from "../../modules/client/application/useRuPhoneModel";
import { useFormFieldErrors } from "../../platform/useFormFieldErrors";
import {
    RU_PHONE_MASKA_PATTERN,
    RU_PHONE_MASKA_TOKENS_ATTR,
    validateRuPhoneForSubmit,
} from "../../platform/ruPhone";
import { mapApiError } from "../../platform/mapApiError";
import { applyApiFieldErrors } from "../../platform/extractApiFieldErrors";
import { useAppDesign } from "../../design/useAppDesign";
import FormField from "../ui/FormField.vue";

const emit = defineEmits(["logged-in"]);

const cli = useAppDesign().components.client;
const s = cli.shared;

const userStore = useUserStore();
const toast = useToast();
const fieldErrors = useFormFieldErrors();
const forgotFieldErrors = useFormFieldErrors();

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

const form = ref({
    phone: "",
    email: "",
    password: "",
});

const { phoneMask } = useRuPhoneModel(form, "phone");

const loading = ref(false);

watch(loginBy, () => {
    fieldErrors.clearField("phone");
    fieldErrors.clearField("email");
});

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
    () => fieldErrors.clearField("password"),
);
watch(forgotEmail, () => forgotFieldErrors.clearField("email"));

async function submit() {
    fieldErrors.clearAll();

    if (!form.value.password) {
        fieldErrors.setFieldError("password", "Введите пароль");
    }

    if (loginBy.value === "phone") {
        const phoneCheck = validateRuPhoneForSubmit(form.value.phone);
        if (!phoneCheck.ok) {
            fieldErrors.setFieldError("phone", phoneCheck.message);
        }
    } else {
        const emailTrim = (form.value.email || "").trim();
        if (!emailTrim) {
            fieldErrors.setFieldError("email", "Введите email");
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailTrim)) {
            fieldErrors.setFieldError("email", "Некорректный формат email");
        }
    }

    if (fieldErrors.hasAny.value) {
        return;
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
            <FormField
                v-if="loginBy === 'phone'"
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
                v-else
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
                            placeholder="you@example.com"
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
        </div>
    </form>
</template>
