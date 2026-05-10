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

const emit = defineEmits(["updated"]);

const cli = useAppDesign().components.client;
const s = cli.shared;

const userStore = useUserStore();

const form = ref({
    name: userStore.profile.name || "",
    phone: userStore.profile.phone || "",
    email: userStore.profile.email || "",
    birth_date: "",
});

const { phoneMask } = useRuPhoneModel(form, "phone");

const loading = ref(false);
const error = ref("");

async function submit() {
    error.value = "";

    if (!form.value.name) {
        error.value = "Имя не может быть пустым";
        return;
    }
    const phoneCheck = validateRuPhoneForSubmit(form.value.phone);
    if (!phoneCheck.ok) {
        error.value = phoneCheck.message;
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
        error.value = mapApiError(
            e,
            "Не удалось сохранить профиль. Попробуйте ещё раз.",
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
            Редактирование профиля
        </h3>

        <div :class="s.fieldStack">
            <div>
                <label :class="s.label">
                    Имя
                </label>
                <input
                    v-model="form.name"
                    type="text"
                    :class="s.input"
                    placeholder="Имя"
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
                    :class="s.input"
                    placeholder="+7 (___) ___-__-__"
                />
            </div>

            <div>
                <label :class="s.label">
                    Email
                </label>
                <input
                    v-model="form.email"
                    type="email"
                    :class="s.input"
                    placeholder="you@example.com"
                />
            </div>

            <div>
                <label :class="s.label">
                    Дата рождения
                </label>
                <input
                    v-model="form.birth_date"
                    type="date"
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
            <span v-if="!loading">Сохранить</span>
            <span v-else>Сохраняем…</span>
        </button>
    </form>
</template>
