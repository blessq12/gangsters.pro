<script setup>
import { ref, watch } from "vue";
import { useUserStore } from "../../modules/client/store/userStore";
import { useFormFieldErrors } from "../../platform/useFormFieldErrors";
import { mapApiError } from "../../platform/mapApiError";
import { applyApiFieldErrors } from "../../platform/extractApiFieldErrors";
import { useAppDesign } from "../../design/useAppDesign";
import FormField from "../ui/FormField.vue";

const emit = defineEmits(["go-login"]);

const cli = useAppDesign().components.client;
const s = cli.shared;

const userStore = useUserStore();
const fieldErrors = useFormFieldErrors();

const email = ref("");
const loading = ref(false);

watch(email, () => fieldErrors.clearField("email"));

async function submit() {
    fieldErrors.clearAll();

    const emailTrim = (email.value || "").trim();
    if (!emailTrim) {
        fieldErrors.setFieldError("email", "Введите email");
        return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailTrim)) {
        fieldErrors.setFieldError("email", "Некорректный формат email");
        return;
    }

    loading.value = true;
    try {
        await userStore.requestPasswordReset(emailTrim);
        email.value = "";
        emit("go-login", { passwordResetSent: true });
    } catch (e) {
        console.error(e);
        if (!applyApiFieldErrors(fieldErrors, e)) {
            fieldErrors.setFormError(
                mapApiError(
                    e,
                    "Не удалось отправить запрос. Попробуй позже.",
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
        <div :class="s.fieldStack">
            <FormField
                label="Email"
                error-size="xs"
                :error="fieldErrors.get('email')"
            >
                <template #default="{ id, invalid, invalidClass, describedBy, ariaInvalid }">
                    <input
                        :id="id"
                        v-model="email"
                        type="email"
                        autocomplete="email"
                        placeholder="you@example.com"
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
            <span v-if="!loading">Отправить</span>
            <span v-else>Отправляем…</span>
        </button>

        <div :class="s.loginFooter">
            <button
                type="button"
                :class="s.loginFooterLink"
                @click="emit('go-login')"
            >
                Назад ко входу
            </button>
        </div>
    </form>
</template>
