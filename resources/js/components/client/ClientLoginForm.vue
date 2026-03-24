<script setup>
import { ref } from "vue";
import { useUserStore } from "../../stores/userStore";
import { useRuPhoneModel } from "../../composables/client/useRuPhoneModel";
import { mapApiError } from "../../utils/api/mapApiError";

const emit = defineEmits(["logged-in"]);

const userStore = useUserStore();

const form = ref({
    phone: "",
    password: "",
});

const phone = useRuPhoneModel(form, "phone");

const loading = ref(false);
const error = ref("");

async function submit() {
    error.value = "";

    if (!form.value.phone) {
        error.value = "Введите номер телефона";
        return;
    }

    if (!form.value.password) {
        error.value = "Введите пароль";
        return;
    }

    loading.value = true;

    try {
        await userStore.loginClient({
            phone: form.value.phone || null,
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
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4 text-slate-50">
        <h3 class="text-base font-semibold text-slate-50">Вход в аккаунт</h3>

        <p class="text-xs text-slate-400">
            Введите телефон и пароль, чтобы войти.
        </p>

        <div class="space-y-3">
            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">
                    Телефон
                </label>
                <input
                    v-model="phone"
                    v-maska
                    data-maska="+7 (###) ###-##-##"
                    type="tel"
                    placeholder="+7 (___) ___-__-__"
                    class="w-full rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-sm text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                />
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">
                    Пароль
                </label>
                <input
                    v-model="form.password"
                    type="password"
                    placeholder="••••••••"
                    class="w-full rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-sm text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                />
            </div>
        </div>

        <p v-if="error" class="text-xs text-red-400">
            {{ error }}
        </p>

        <button
            type="submit"
            :disabled="loading"
            class="inline-flex w-full items-center justify-center rounded-xl bg-amber-400 px-4 py-2 text-sm font-semibold text-black shadow-[0_0_18px_rgba(251,191,36,0.75)] transition hover:bg-amber-300 disabled:opacity-60 disabled:shadow-none"
        >
            <span v-if="!loading">Войти</span>
            <span v-else>Входим…</span>
        </button>
    </form>
</template>
