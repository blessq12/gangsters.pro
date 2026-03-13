<script setup>
import { computed, ref } from "vue";
import { useUserStore } from "../../stores/userStore";

const userStore = useUserStore();

const form = ref({
    title: "",
    street: "",
    house: "",
    apartment: "",
    comment: "",
    make_default: false,
});

const loading = ref(false);
const error = ref("");

const hasAddresses = computed(
    () => Array.isArray(userStore.addresses) && userStore.addresses.length > 0,
);

async function addAddress() {
    error.value = "";

    if (!form.value.street || !form.value.house) {
        error.value = "Укажи улицу и дом";
        return;
    }

    loading.value = true;

    try {
        await userStore.addClientAddress({
            title: form.value.title || null,
            street: form.value.street,
            house: form.value.house,
            apartment: form.value.apartment || null,
            comment: form.value.comment || null,
            make_default: form.value.make_default,
        });

        form.value = {
            title: "",
            street: "",
            house: "",
            apartment: "",
            comment: "",
            make_default: false,
        };
    } catch (e) {
        console.error(e);
        error.value =
            e?.response?.data?.message ||
            "Не удалось сохранить адрес. Попробуй ещё раз.";
    } finally {
        loading.value = false;
    }
}

async function removeAddress(id) {
    try {
        await userStore.deleteClientAddress(id);
    } catch (e) {
        console.error(e);
        error.value =
            e?.response?.data?.message ||
            "Не удалось удалить адрес. Попробуй ещё раз.";
    }
}

function useAddress(id) {
    userStore.selectAddress(id);
}
</script>

<template>
    <div class="space-y-3 text-slate-50">
        <div v-if="hasAddresses" class="space-y-2 text-xs">
            <div
                v-for="address in userStore.addresses"
                :key="address.id"
                class="rounded-2xl border border-white/10 bg-black/40 px-3 py-2"
            >
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <p class="font-medium text-slate-50">
                            {{ address.title || "Адрес #" + address.id }}
                        </p>
                        <p class="mt-1 text-slate-300">
                            {{ address.street }}, д. {{ address.house }}
                            <span v-if="address.apartment">
                                , кв. {{ address.apartment }}
                            </span>
                        </p>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <button
                            type="button"
                            class="rounded-full bg-white/5 px-2 py-0.5 text-[10px] text-slate-200 hover:bg-white/10"
                            @click="useAddress(address.id)"
                        >
                            {{ userStore.selectedAddressId === address.id ? "Выбран" : "Выбрать" }}
                        </button>
                        <button
                            type="button"
                            class="text-[10px] text-slate-500 hover:text-red-400"
                            @click="removeAddress(address.id)"
                        >
                            удалить
                        </button>
                    </div>
                </div>
                <p v-if="address.comment" class="mt-1 text-[11px] text-slate-400">
                    {{ address.comment }}
                </p>
            </div>
        </div>

        <p
            v-else
            class="text-xs text-slate-400"
        >
            Адреса ещё не добавлены. Укажи адрес здесь или при оформлении заказа — мы его
            запомним.
        </p>

        <form
            class="mt-3 space-y-2 border-t border-white/5 pt-2 text-xs"
            @submit.prevent="addAddress"
        >
            <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">
                Новый адрес
            </p>

            <div class="grid grid-cols-2 gap-2">
                <input
                    v-model="form.title"
                    type="text"
                    placeholder="Название (дом, работа)"
                    class="col-span-2 rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-[11px] text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                />
                <input
                    v-model="form.street"
                    type="text"
                    placeholder="Улица"
                    class="col-span-2 rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-[11px] text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                />
                <input
                    v-model="form.house"
                    type="text"
                    placeholder="Дом"
                    class="rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-[11px] text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                />
                <input
                    v-model="form.apartment"
                    type="text"
                    placeholder="Квартира"
                    class="rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-[11px] text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                />
            </div>

            <textarea
                v-model="form.comment"
                rows="2"
                placeholder="Комментарий для курьера (подъезд, код, ориентир)"
                class="w-full rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-[11px] text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
            />

            <label class="flex items-center gap-2 text-[11px] text-slate-300">
                <input
                    v-model="form.make_default"
                    type="checkbox"
                    class="h-3.5 w-3.5 rounded border-white/20 bg-black/60 text-amber-400 focus:ring-amber-400/60"
                />
                <span>Сделать основным адресом</span>
            </label>

            <p
                v-if="error"
                class="text-[11px] text-red-400"
            >
                {{ error }}
            </p>

            <button
                type="submit"
                :disabled="loading"
                class="inline-flex w-full items-center justify-center rounded-xl bg-amber-400 px-3 py-1.5 text-[11px] font-semibold text-black shadow-[0_0_14px_rgba(251,191,36,0.7)] transition hover:bg-amber-300 disabled:opacity-60 disabled:shadow-none"
            >
                <span v-if="!loading">Сохранить адрес</span>
                <span v-else>Сохраняем…</span>
            </button>
        </form>
    </div>
</template>

