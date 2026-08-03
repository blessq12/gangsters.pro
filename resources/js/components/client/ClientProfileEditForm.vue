<script setup>
import { ref, watch } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useRuPhoneModel } from "../../modules/client/application/useRuPhoneModel";
import { useUserStore } from "../../modules/client/store/userStore";
import { applyApiFieldErrors } from "../../platform/extractApiFieldErrors";
import { mapApiError } from "../../platform/mapApiError";
import {
    RU_PHONE_MASKA_PATTERN,
    RU_PHONE_MASKA_TOKENS_ATTR,
    validateRuPhoneForSubmit,
} from "../../platform/ruPhone";
import { useFormFieldErrors } from "../../platform/useFormFieldErrors";
import FormField from "../ui/FormField.vue";

const emit = defineEmits(["updated", "logout"]);

const cli = useAppDesign().components.client;
const s = cli.shared;
const pv = cli.profileView;

const userStore = useUserStore();
const fieldErrors = useFormFieldErrors();

const form = ref({
    name: userStore.profile.name || "",
    phone: userStore.profile.phone || "",
    email: userStore.profile.email || "",
    birth_date: "",
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
    () => form.value.birth_date,
    () => fieldErrors.clearField("birth_date"),
);

async function submit() {
    fieldErrors.clearAll();

    if (!form.value.name) {
        fieldErrors.setFieldError("name", "Имя не может быть пустым");
    }

    const phoneCheck = validateRuPhoneForSubmit(form.value.phone);
    if (!phoneCheck.ok) {
        fieldErrors.setFieldError("phone", phoneCheck.message);
    }

    if (fieldErrors.hasAny.value) {
        return;
    }

    loading.value = true;

    try {
        await userStore.updateClientProfile({
            name: form.value.name,
            phone: form.value.phone,
            email: form.value.email || null,
            birth_date: form.value.birth_date || null,
        });

        emit("updated");
    } catch (e) {
        console.error(e);
        if (!applyApiFieldErrors(fieldErrors, e)) {
            fieldErrors.setFormError(
                mapApiError(
                    e,
                    "Не удалось сохранить профиль. Попробуйте ещё раз.",
                ),
            );
        }
    } finally {
        loading.value = false;
    }
}

function handleLogoutClick() {
    userStore.clearAuth();
    emit("logout");
}
</script>

<template>
    <form :class="s.formRoot" @submit.prevent="submit">
        <div :class="s.fieldStack">
            <FormField
                label="Имя"
                error-size="xs"
                :error="fieldErrors.get('name')"
            >
                <template
                    #default="{
                        id,
                        invalid,
                        invalidClass,
                        describedBy,
                        ariaInvalid,
                    }"
                >
                    <input
                        :id="id"
                        v-model="form.name"
                        type="text"
                        :class="[s.input, invalid && invalidClass]"
                        placeholder="Имя"
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
                <template
                    #default="{
                        id,
                        invalid,
                        invalidClass,
                        describedBy,
                        ariaInvalid,
                    }"
                >
                    <input
                        :id="id"
                        v-model="phoneMask.masked"
                        v-maska="phoneMask"
                        :data-maska="RU_PHONE_MASKA_PATTERN"
                        :data-maska-tokens="RU_PHONE_MASKA_TOKENS_ATTR"
                        type="tel"
                        :class="[s.input, invalid && invalidClass]"
                        placeholder="+7 (___) ___-__-__"
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
                <template
                    #default="{
                        id,
                        invalid,
                        invalidClass,
                        describedBy,
                        ariaInvalid,
                    }"
                >
                    <input
                        :id="id"
                        v-model="form.email"
                        type="email"
                        :class="[s.input, invalid && invalidClass]"
                        placeholder="you@example.com"
                        :aria-invalid="ariaInvalid"
                        :aria-describedby="describedBy"
                    />
                </template>
            </FormField>

            <FormField
                label="Дата рождения"
                error-size="xs"
                :error="fieldErrors.get('birth_date')"
            >
                <template
                    #default="{
                        id,
                        invalid,
                        invalidClass,
                        describedBy,
                        ariaInvalid,
                    }"
                >
                    <input
                        :id="id"
                        v-model="form.birth_date"
                        type="date"
                        :class="[s.input, invalid && invalidClass]"
                        :aria-invalid="ariaInvalid"
                        :aria-describedby="describedBy"
                    />
                </template>
            </FormField>
        </div>

        <p v-if="fieldErrors.formError" :class="s.errorXs">
            {{ fieldErrors.formError }}
        </p>

        <button type="submit" :disabled="loading" :class="s.btnPrimaryWide">
            <span v-if="!loading">Сохранить</span>
            <span v-else>Сохраняем…</span>
        </button>
    </form>
</template>
