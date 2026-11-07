<script setup>
import { computed, onMounted, ref } from "vue";
import { storeToRefs } from "pinia";
import StopsMap from "../../StopsMap.vue";
import { paymentMethodLabel, useOpsBoard } from "../../opsBoard";

const board = useOpsBoard();
const { routeSheets } = storeToRefs(board);
const openRunId = ref(null);
const failReasons = ref({});
const pageError = ref("");

const waiting = computed(() => routeSheets.value.filter((sheet) => sheet.status === "released"));
const delivering = computed(() => routeSheets.value.filter((sheet) => sheet.status === "in_delivery"));
const run = computed(() =>
    delivering.value.find((entry) => entry.id === openRunId.value) ?? null,
);
const canComplete = computed(() =>
    run.value != null && run.value.stops.every((stop) => stop.outcome !== "pending"),
);
const runPoints = computed(() =>
    (run.value?.stops ?? [])
        .filter((stop) => stop.outcome === "pending" && stop.lat != null && stop.lng != null)
        .map((stop) => ({
            lat: stop.lat,
            lng: stop.lng,
            label: `#${stop.orderId}`,
        })),
);

onMounted(async () => {
    try {
        await board.loadRouteSheets();
    } catch (error) {
        pageError.value = error instanceof Error ? error.message : "Не удалось связаться с сервером.";
    }
});

function reasonKey(orderId) {
    return `${openRunId.value}:${orderId}`;
}

async function take(id) {
    pageError.value = "";
    try {
        await board.takeRouteSheet(id);
        openRunId.value = id;
    } catch (error) {
        pageError.value = error instanceof Error ? error.message : "Не удалось взять лист.";
    }
}

async function settle(orderId, outcome, reason) {
    if (openRunId.value == null) {
        return;
    }
    pageError.value = "";
    try {
        await board.settleStop(openRunId.value, orderId, outcome, reason);
    } catch (error) {
        pageError.value = error instanceof Error ? error.message : "Не удалось сохранить статус.";
    }
}

async function complete() {
    if (openRunId.value == null) {
        return;
    }
    pageError.value = "";
    try {
        await board.completeRouteSheet(openRunId.value);
        openRunId.value = null;
    } catch (error) {
        pageError.value = error instanceof Error ? error.message : "Не удалось завершить лист.";
    }
}
</script>

<template>
    <section class="space-y-4">
        <h1 class="text-xl">Транспортные листы</h1>
        <p
            v-if="pageError"
            class="text-sm text-red-700"
        >
            {{ pageError }}
        </p>

        <div
            v-if="!run"
            class="space-y-4"
        >
            <div class="space-y-2">
                <h2 class="text-sm font-medium">Ждёт курьера</h2>
                <p
                    v-if="waiting.length === 0"
                    class="text-sm text-neutral-500"
                >
                    Листов нет.
                </p>
                <div
                    v-for="entry in waiting"
                    :key="entry.id"
                    class="flex items-center justify-between gap-3 rounded border border-neutral-200 bg-white px-3 py-3 text-sm"
                >
                    <span>Лист #{{ entry.id }} · остановок {{ entry.stops.length }}</span>
                    <button
                        type="button"
                        class="inline-flex h-9 items-center rounded bg-neutral-900 px-3 text-sm text-white"
                        @click="take(entry.id)"
                    >
                        В доставку
                    </button>
                </div>
            </div>
            <div class="space-y-2">
                <h2 class="text-sm font-medium">В доставке</h2>
                <p
                    v-if="delivering.length === 0"
                    class="text-sm text-neutral-500"
                >
                    Взятых листов нет.
                </p>
                <button
                    v-for="entry in delivering"
                    :key="entry.id"
                    type="button"
                    class="block w-full rounded border border-neutral-200 bg-white px-3 py-3 text-left text-sm"
                    @click="openRunId = entry.id"
                >
                    Лист #{{ entry.id }} · остановок {{ entry.stops.length }}
                </button>
            </div>
        </div>

        <div
            v-else
            class="space-y-4"
        >
            <button
                type="button"
                class="inline-flex h-9 items-center text-sm text-neutral-600"
                @click="openRunId = null"
            >
                Все листы
            </button>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg">Лист #{{ run.id }}</h2>
                <button
                    type="button"
                    class="inline-flex h-9 items-center rounded bg-neutral-900 px-3 text-sm text-white disabled:opacity-40"
                    :disabled="!canComplete"
                    @click="complete"
                >
                    Завершить лист
                </button>
            </div>
            <StopsMap :points="runPoints" />
            <div class="overflow-x-auto rounded border border-neutral-200 bg-white">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="border-b border-neutral-200 bg-neutral-50 text-xs text-neutral-500">
                        <tr>
                            <th class="px-3 py-2 font-medium">#</th>
                            <th class="px-3 py-2 font-medium">Заказ</th>
                            <th class="px-3 py-2 font-medium">Адрес</th>
                            <th class="px-3 py-2 font-medium">Сумма</th>
                            <th class="px-3 py-2 font-medium">Исход</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(stop, index) in run.stops"
                            :key="stop.orderId"
                            class="border-b border-neutral-100 align-top last:border-0"
                        >
                            <td class="px-3 py-2">{{ index + 1 }}</td>
                            <td class="px-3 py-2">
                                <div>#{{ stop.orderId }} {{ stop.clientName }}</div>
                                <div class="text-xs text-neutral-500">{{ stop.phone }}</div>
                            </td>
                            <td class="px-3 py-2">{{ stop.address }}</td>
                            <td class="whitespace-nowrap px-3 py-2">
                                <div>{{ stop.total }} ₽</div>
                                <div class="text-xs text-neutral-500">{{ paymentMethodLabel(stop.payment) }}</div>
                            </td>
                            <td class="px-3 py-2">
                                <p
                                    v-if="stop.outcome !== 'pending'"
                                    class="text-sm"
                                >
                                    {{ stop.outcome === "delivered" ? "Выдан" : `Не выдан: ${stop.reason}` }}
                                </p>
                                <div
                                    v-else
                                    class="space-y-2"
                                >
                                    <button
                                        type="button"
                                        class="inline-flex h-9 items-center rounded bg-neutral-900 px-3 text-sm text-white"
                                        @click="settle(stop.orderId, 'delivered', '')"
                                    >
                                        Выдан
                                    </button>
                                    <form
                                        class="flex gap-2"
                                        @submit.prevent="settle(stop.orderId, 'failed', failReasons[reasonKey(stop.orderId)] ?? '')"
                                    >
                                        <input
                                            v-model="failReasons[reasonKey(stop.orderId)]"
                                            type="text"
                                            required
                                            placeholder="Почему не выдан"
                                            class="h-9 min-w-0 flex-1 rounded border border-neutral-300 px-3 text-sm"
                                        >
                                        <button
                                            type="submit"
                                            class="inline-flex h-9 items-center rounded border border-neutral-300 px-3 text-sm"
                                        >
                                            Не выдан
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</template>
