<script setup>
import { computed, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useToast } from "vue-toastification";
import { useUserStore } from "../stores/userStore";
import { mapApiError } from "../utils/api/mapApiError";
import { useAppDesign } from "../design/useAppDesign";

const route = useRoute();
const router = useRouter();
const userStore = useUserStore();
const toast = useToast();

const token = computed(() => {
    const t = route.query.token;
    if (Array.isArray(t)) {
        return t[0] || "";
    }
    return typeof t === "string" ? t : "";
});

const password = ref("");
const confirmPassword = ref("");
const loading = ref(false);
const error = ref("");

const rp = useAppDesign().components.pages.resetPassword;

async function submit() {
    error.value = "";

    if (!token.value) {
        error.value = "Нет токена в ссылке. Запроси письмо со сбросом ещё раз.";
        return;
    }
    if (!password.value || password.value.length < 6) {
        error.value = "Пароль не короче 6 символов";
        return;
    }
    if (password.value !== confirmPassword.value) {
        error.value = "Пароли не совпадают";
        return;
    }

    loading.value = true;
    try {
        await userStore.changePasswordWithToken({
            token: token.value,
            password: password.value,
        });
        userStore.clearAuth();
        toast.success("Пароль обновлён. Войди с новым паролём.");
        await router.replace({ name: "home" });
    } catch (e) {
        console.error(e);
        error.value = mapApiError(
            e,
            "Не удалось сменить пароль. Ссылка могла устареть — запроси новую.",
        );
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div :class="rp.pageWrap">
        <h1 :class="rp.title">
            Новый пароль
        </h1>
        <p :class="rp.lead">
            Придумай пароль для входа в личный кабинет.
        </p>

        <form
            v-if="token"
            :class="rp.form"
            @submit.prevent="submit"
        >
            <div>
                <label :class="rp.label">
                    Пароль
                </label>
                <input
                    v-model="password"
                    type="password"
                    autocomplete="new-password"
                    placeholder="минимум 6 символов"
                    :class="rp.input"
                />
            </div>
            <div>
                <label :class="rp.label">
                    Подтверждение
                </label>
                <input
                    v-model="confirmPassword"
                    type="password"
                    autocomplete="new-password"
                    placeholder="ещё раз"
                    :class="rp.input"
                />
            </div>
            <p
                v-if="error"
                :class="rp.error"
            >
                {{ error }}
            </p>
            <button
                type="submit"
                :disabled="loading"
                :class="rp.submitBtn"
            >
                <span v-if="!loading">Сохранить пароль</span>
                <span v-else>Сохраняем…</span>
            </button>
        </form>

        <div
            v-else
            :class="rp.noTokenCard"
        >
            <p :class="rp.noTokenLead">
                Ссылка неполная: нет токена. Открой ссылку из письма или запроси
                сброс пароля снова в форме входа.
            </p>
            <RouterLink
                :to="{ name: 'home' }"
                :class="rp.homeLink"
            >
                На главную
            </RouterLink>
        </div>
    </div>
</template>
