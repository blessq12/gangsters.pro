<script setup>
import { ref } from "vue";
import { useUserStore } from "../../stores/userStore";
import { useRuPhoneModel } from "../../composables/client/useRuPhoneModel";
import { mapApiError } from "../../utils/api/mapApiError";

const emit = defineEmits(["updated"]);

const userStore = useUserStore();

const form = ref({
    name: userStore.profile.name || "",
    phone: userStore.profile.phone || "",
    email: userStore.profile.email || "",
    birth_date: "",
});

const phone = useRuPhoneModel(form, "phone");

const loading = ref(false);
const error = ref("");

async function submit() {
    error.value = "";

    if (!form.value.name) {
        error.value = "Имя не может быть пустым";
        return;
    }
    if (!form.value.phone) {
        error.value = "Телефон обязателен";
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
    <form @submit.prevent="submit" class="space-y-4 text-slate-50">
        <h3 class="text-base font-semibold text-slate-50">
            Редактирование профиля
        </h3>

        <div class="space-y-3">
            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">
                    Имя
                </label>
                <input
                    v-model="form.name"
                    type="text"
                    class="w-full rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-sm text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                    placeholder="Имя"
                />
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">
                    Телефон
                </label>
                <input
                    v-model="phone"
                    v-maska
                    data-maska="+7 (###) ###-##-##"
                    type="tel"
                    class="w-full rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-sm text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                    placeholder="+7 (___) ___-__-__"
                />
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">
                    Email
                </label>
                <input
                    v-model="form.email"
                    type="email"
                    class="w-full rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-sm text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                    placeholder="you@example.com"
                />
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">
                    Дата рождения
                </label>
                <input
                    v-model="form.birth_date"
                    type="date"
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
            <span v-if="!loading">Сохранить</span>
            <span v-else>Сохраняем…</span>
        </button>
    </form>
</template>
