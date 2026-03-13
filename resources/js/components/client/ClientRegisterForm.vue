<script setup>
import { computed, ref } from "vue";
import { useUserStore } from "../../stores/userStore";

const emit = defineEmits(["registered"]);

const userStore = useUserStore();

const form = ref({
    name: "",
    phone: "",
    email: "",
    birth_date: "",
    password: "",
    consent_personal_data: true,
    consent_marketing: false,
});

const phone = computed({
    get() {
        return form.value.phone;
    },
    set(value) {
        let digits = String(value || "").replace(/\D/g, "");
        if (digits.length && (digits[0] === "7" || digits[0] === "8")) {
            digits = digits.slice(1);
        }
        form.value.phone = digits;
    },
});

const loading = ref(false);
const error = ref("");

async function submit() {
    error.value = "";

    if (!form.value.name) {
        error.value = "Введите имя";
        return;
    }
    if (!form.value.phone) {
        error.value = "Введите номер телефона";
        return;
    }
    if (!form.value.password) {
        error.value = "Придумайте пароль";
        return;
    }
    if (!form.value.consent_personal_data) {
        error.value = "Нужно согласиться на обработку персональных данных";
        return;
    }

    loading.value = true;

    try {
        await userStore.registerClient({
            ...form.value,
        });

        emit("registered");
    } catch (e) {
        console.error(e);
        error.value =
            e?.response?.data?.message ||
            "Не удалось завершить регистрацию. Попробуйте ещё раз.";
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4 text-slate-50">
        <h3 class="text-base font-semibold text-slate-50">Регистрация</h3>

        <p class="text-xs text-slate-400">
            Создаём аккаунт, чтобы сохранять адреса и заказы.
        </p>

        <div class="space-y-3">
            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">
                    Имя
                </label>
                <input
                    v-model="form.name"
                    type="text"
                    placeholder="Как к тебе обращаться?"
                    class="w-full rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-sm text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
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
                    placeholder="+7 (___) ___-__-__"
                    class="w-full rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-sm text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                />
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">
                    Email (опционально)
                </label>
                <input
                    v-model="form.email"
                    type="email"
                    placeholder="you@example.com"
                    class="w-full rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-sm text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                />
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">
                    Дата рождения (опционально)
                </label>
                <input
                    v-model="form.birth_date"
                    type="date"
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
                    placeholder="минимум 6 символов"
                    class="w-full rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-sm text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                />
            </div>

            <div class="space-y-1">
                <label class="flex items-center gap-2 text-xs text-slate-300">
                    <input
                        v-model="form.consent_personal_data"
                        type="checkbox"
                        class="h-4 w-4 rounded border-white/20 bg-black/60 text-amber-400 focus:ring-amber-400/60"
                    />
                    <span>Согласен на обработку персональных данных</span>
                </label>
                <label class="flex items-center gap-2 text-xs text-slate-400">
                    <input
                        v-model="form.consent_marketing"
                        type="checkbox"
                        class="h-4 w-4 rounded border-white/20 bg-black/60 text-amber-400 focus:ring-amber-400/60"
                    />
                    <span>Получать новости и акции</span>
                </label>
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
            <span v-if="!loading">Зарегистрироваться</span>
            <span v-else>Создаём аккаунт…</span>
        </button>
    </form>
</template>
