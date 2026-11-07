<script setup>
import { onMounted, ref } from "vue";
import { backofficeRequest } from "../../api";

const days = [
    { id: 1, label: "Пн" },
    { id: 2, label: "Вт" },
    { id: 3, label: "Ср" },
    { id: 4, label: "Чт" },
    { id: 5, label: "Пт" },
    { id: 6, label: "Сб" },
    { id: 7, label: "Вс" },
];

const form = ref(null);
const error = ref("");
const saved = ref(false);

onMounted(load);

async function load() {
    error.value = "";
    try {
        const payload = await backofficeRequest("GET", "/api/backoffice/promotion-policy");
        form.value = payload.policy;
    } catch (reason) {
        error.value = reason instanceof Error ? reason.message : "Не удалось связаться с сервером.";
    }
}

function toggleDay(day) {
    const current = new Set(form.value.gift_courier_weekdays);
    if (current.has(day)) {
        current.delete(day);
    } else {
        current.add(day);
    }
    form.value.gift_courier_weekdays = [...current].sort((left, right) => left - right);
}

async function submit() {
    saved.value = false;
    error.value = "";
    try {
        const payload = await backofficeRequest("PUT", "/api/backoffice/promotion-policy", form.value);
        form.value = payload.policy;
        saved.value = true;
    } catch (reason) {
        error.value = reason instanceof Error ? reason.message : "Не удалось связаться с сервером.";
    }
}
</script>

<template>
    <section class="space-y-4">
        <h1 class="text-xl">Правила акций</h1>
        <p
            v-if="error"
            class="text-sm text-red-700"
        >
            {{ error }}
        </p>
        <form
            v-else-if="form"
            class="space-y-4"
            @submit.prevent="submit"
        >
            <div class="space-y-2">
                <h2 class="text-sm font-medium">Подарок</h2>
                <label class="flex items-center gap-2 text-sm">
                    <input
                        v-model="form.gift_benefit_active"
                        type="checkbox"
                    >
                    Правило подарка активно
                </label>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="block text-sm">Самовывоз от, ₽
                        <input
                            v-model.number="form.gift_pickup_min_order_rubles"
                            type="number"
                            min="1"
                            required
                            class="mt-1 h-9 w-full rounded border border-neutral-300 px-3 text-sm"
                        >
                    </label>
                    <label class="block text-sm">Доставка от, ₽
                        <input
                            v-model.number="form.gift_courier_min_order_rubles"
                            type="number"
                            min="1"
                            required
                            class="mt-1 h-9 w-full rounded border border-neutral-300 px-3 text-sm"
                        >
                    </label>
                </div>
                <div class="flex flex-wrap gap-1">
                    <button
                        v-for="day in days"
                        :key="day.id"
                        type="button"
                        class="inline-flex h-9 items-center rounded px-3 text-sm"
                        :class="form.gift_courier_weekdays.includes(day.id) ? 'bg-neutral-900 text-white' : 'bg-white text-neutral-700'"
                        @click="toggleDay(day.id)"
                    >
                        {{ day.label }}
                    </button>
                </div>
            </div>
            <div class="space-y-2">
                <h2 class="text-sm font-medium">Доставка</h2>
                <label class="flex items-center gap-2 text-sm">
                    <input
                        v-model="form.delivery_benefit_active"
                        type="checkbox"
                    >
                    Политика доставки активна
                </label>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="block text-sm">Бесплатно от, ₽
                        <input
                            v-model.number="form.delivery_free_threshold_rubles"
                            type="number"
                            min="0"
                            required
                            class="mt-1 h-9 w-full rounded border border-neutral-300 px-3 text-sm"
                        >
                    </label>
                    <label class="block text-sm">Доплата вне зоны, ₽
                        <input
                            v-model.number="form.delivery_outside_zone_surcharge_rubles"
                            type="number"
                            min="0"
                            required
                            class="mt-1 h-9 w-full rounded border border-neutral-300 px-3 text-sm"
                        >
                    </label>
                </div>
            </div>
            <div class="space-y-2">
                <h2 class="text-sm font-medium">Комплект</h2>
                <label class="flex items-center gap-2 text-sm">
                    <input
                        v-model="form.complement_set_benefit_active"
                        type="checkbox"
                    >
                    Правило комплекта активно
                </label>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="block text-sm">Роллов на один комплект
                        <input
                            v-model.number="form.complement_set_rolls_per_set"
                            type="number"
                            min="1"
                            required
                            class="mt-1 h-9 w-full rounded border border-neutral-300 px-3 text-sm"
                        >
                    </label>
                    <label class="block text-sm">Макс. комплектов
                        <input
                            v-model.number="form.complement_set_max_sets"
                            type="number"
                            min="1"
                            required
                            class="mt-1 h-9 w-full rounded border border-neutral-300 px-3 text-sm"
                        >
                    </label>
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
        <p
            v-else
            class="text-sm text-neutral-500"
        >
            Загрузка…
        </p>
    </section>
</template>
