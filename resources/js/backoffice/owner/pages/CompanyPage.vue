<script setup>
import { onMounted, ref } from "vue";
import { backofficeRequest } from "../../api";

const days = [
    { id: "mon", label: "Пн" },
    { id: "tue", label: "Вт" },
    { id: "wed", label: "Ср" },
    { id: "thu", label: "Чт" },
    { id: "fri", label: "Пт" },
    { id: "sat", label: "Сб" },
    { id: "sun", label: "Вс" },
];

const form = ref(null);
const error = ref("");
const saved = ref(false);

onMounted(load);

async function load() {
    error.value = "";
    try {
        const payload = await backofficeRequest("GET", "/api/backoffice/company");
        form.value = payload.company;
    } catch (reason) {
        error.value = reason instanceof Error ? reason.message : "Не удалось связаться с сервером.";
    }
}

async function submit() {
    saved.value = false;
    error.value = "";
    try {
        const payload = await backofficeRequest("PUT", "/api/backoffice/company", form.value);
        form.value = payload.company;
        saved.value = true;
    } catch (reason) {
        error.value = reason instanceof Error ? reason.message : "Не удалось связаться с сервером.";
    }
}
</script>

<template>
    <section class="space-y-4">
        <h1 class="text-xl">Компания</h1>
        <p
            v-if="error"
            class="text-sm text-red-700"
        >
            {{ error }}
        </p>
        <p
            v-else-if="!form"
            class="text-sm text-neutral-500"
        >
            Загрузка…
        </p>
        <form
            v-else
            class="space-y-4"
            @submit.prevent="submit"
        >
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="block text-sm">Название
                    <input
                        v-model="form.name"
                        required
                        class="mt-1 h-9 w-full rounded border border-neutral-300 px-3 text-sm"
                    >
                </label>
                <label class="block text-sm">Телефон
                    <input
                        v-model="form.phone"
                        class="mt-1 h-9 w-full rounded border border-neutral-300 px-3 text-sm"
                    >
                </label>
                <label class="block text-sm sm:col-span-2">Описание
                    <textarea
                        v-model="form.description"
                        rows="3"
                        class="mt-1 w-full rounded border border-neutral-300 px-3 py-2 text-sm"
                    />
                </label>
                <label class="block text-sm">Email
                    <input
                        v-model="form.email"
                        class="mt-1 h-9 w-full rounded border border-neutral-300 px-3 text-sm"
                    >
                </label>
                <label class="block text-sm">Сайт
                    <input
                        v-model="form.socials.site_url"
                        class="mt-1 h-9 w-full rounded border border-neutral-300 px-3 text-sm"
                    >
                </label>
                <label class="block text-sm">Telegram
                    <input
                        v-model="form.socials.telegram"
                        class="mt-1 h-9 w-full rounded border border-neutral-300 px-3 text-sm"
                    >
                </label>
                <label class="block text-sm">VK
                    <input
                        v-model="form.socials.vk"
                        class="mt-1 h-9 w-full rounded border border-neutral-300 px-3 text-sm"
                    >
                </label>
                <label class="block text-sm">Instagram
                    <input
                        v-model="form.socials.inst"
                        class="mt-1 h-9 w-full rounded border border-neutral-300 px-3 text-sm"
                    >
                </label>
            </div>
            <div class="space-y-2">
                <h2 class="text-sm font-medium">Расписание</h2>
                <div
                    v-for="day in days"
                    :key="day.id"
                    class="flex items-center gap-3"
                >
                    <span class="w-8 text-sm">{{ day.label }}</span>
                    <input
                        v-model="form.schedule[day.id].work"
                        placeholder="Часы"
                        class="h-9 w-40 rounded border border-neutral-300 px-3 text-sm"
                    >
                    <label class="flex items-center gap-2 text-sm">
                        <input
                            v-model="form.schedule[day.id].is_day_off"
                            type="checkbox"
                        >
                        Выходной
                    </label>
                </div>
            </div>
            <div class="grid gap-3 sm:grid-cols-3">
                <label class="block text-sm sm:col-span-3">Полное наименование
                    <input
                        v-model="form.legal.full_name"
                        class="mt-1 h-9 w-full rounded border border-neutral-300 px-3 text-sm"
                    >
                </label>
                <label class="block text-sm">ИНН
                    <input
                        v-model="form.legal.inn"
                        class="mt-1 h-9 w-full rounded border border-neutral-300 px-3 text-sm"
                    >
                </label>
                <label class="block text-sm">ОГРН
                    <input
                        v-model="form.legal.ogrn"
                        class="mt-1 h-9 w-full rounded border border-neutral-300 px-3 text-sm"
                    >
                </label>
            </div>
            <button
                type="submit"
                class="inline-flex h-9 items-center rounded bg-neutral-900 px-3 text-sm text-white"
            >
                Сохранить
            </button>
            <p
                v-if="saved"
                class="text-sm text-neutral-500"
            >
                Сохранено.
            </p>
        </form>
    </section>
</template>
