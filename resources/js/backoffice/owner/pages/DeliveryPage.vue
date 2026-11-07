<script setup>
import { onMounted, ref } from "vue";
import { backofficeRequest } from "../../api";

const form = ref(null);
const error = ref("");
const saved = ref(false);

onMounted(load);

async function load() {
    error.value = "";
    try {
        const payload = await backofficeRequest("GET", "/api/backoffice/delivery-settings");
        form.value = payload.delivery;
    } catch (reason) {
        error.value = reason instanceof Error ? reason.message : "Не удалось связаться с сервером.";
    }
}

async function submit() {
    saved.value = false;
    error.value = "";
    try {
        const payload = await backofficeRequest("PUT", "/api/backoffice/delivery-settings", form.value);
        form.value = payload.delivery;
        saved.value = true;
    } catch (reason) {
        error.value = reason instanceof Error ? reason.message : "Не удалось связаться с сервером.";
    }
}
</script>

<template>
    <section class="space-y-4">
        <h1 class="text-xl">Доставка</h1>
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
                <label class="block text-sm">Среднее время, мин
                    <input
                        v-model.number="form.average_delivery_time_minutes"
                        type="number"
                        min="0"
                        class="mt-1 h-9 w-full rounded border border-neutral-300 px-3 text-sm"
                    >
                </label>
                <label class="block text-sm">Минимальный заказ, ₽
                    <input
                        v-model.number="form.min_order_rubles"
                        type="number"
                        min="0"
                        class="mt-1 h-9 w-full rounded border border-neutral-300 px-3 text-sm"
                    >
                </label>
                <label class="block text-sm">Город
                    <input
                        v-model="form.kitchen.city"
                        class="mt-1 h-9 w-full rounded border border-neutral-300 px-3 text-sm"
                    >
                </label>
                <label class="block text-sm">Улица
                    <input
                        v-model="form.kitchen.street"
                        class="mt-1 h-9 w-full rounded border border-neutral-300 px-3 text-sm"
                    >
                </label>
                <label class="block text-sm">Дом
                    <input
                        v-model="form.kitchen.house"
                        class="mt-1 h-9 w-full rounded border border-neutral-300 px-3 text-sm"
                    >
                </label>
                <label class="block text-sm">Комментарий к адресу
                    <input
                        v-model="form.kitchen.comment"
                        class="mt-1 h-9 w-full rounded border border-neutral-300 px-3 text-sm"
                    >
                </label>
                <label class="block text-sm sm:col-span-2">Адрес для поиска на карте
                    <input
                        v-model="form.kitchen.search_line"
                        class="mt-1 h-9 w-full rounded border border-neutral-300 px-3 text-sm"
                    >
                </label>
            </div>
            <div class="space-y-2">
                <h2 class="text-sm font-medium">Зоны доставки</h2>
                <p class="text-sm text-neutral-500">Полигон зоны остаётся как нарисован. Здесь меняются название, тариф и отдалённость.</p>
                <div class="overflow-x-auto rounded border border-neutral-200 bg-white">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-neutral-200 bg-neutral-50 text-xs text-neutral-500">
                            <tr>
                                <th class="px-3 py-2 font-medium">Название</th>
                                <th class="px-3 py-2 font-medium">Тариф, ₽</th>
                                <th class="px-3 py-2 font-medium">Отдалённый район</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="zone in form.zones"
                                :key="zone.id"
                                class="border-b border-neutral-100 last:border-0"
                            >
                                <td class="px-3 py-2">
                                    <input
                                        v-model="zone.name"
                                        required
                                        class="h-9 w-full rounded border border-neutral-300 px-3 text-sm"
                                    >
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        v-model.number="zone.fee_rubles"
                                        type="number"
                                        min="0"
                                        required
                                        class="h-9 w-32 rounded border border-neutral-300 px-3 text-sm"
                                    >
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        v-model="zone.is_remote"
                                        type="checkbox"
                                    >
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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
