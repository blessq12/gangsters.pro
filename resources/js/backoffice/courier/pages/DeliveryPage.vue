<script setup>
import { computed, ref } from "vue";
import { storeToRefs } from "pinia";
import StopsMap from "../../StopsMap.vue";
import { useOpsBoard } from "../../opsBoard";

const board = useOpsBoard();
const { releasedRuns } = storeToRefs(board);
const openRunId = ref(null);
const failReasons = ref({});

const run = computed(() =>
    releasedRuns.value.find((entry) => entry.id === openRunId.value) ?? null,
);

const runPoints = computed(() =>
    (run.value?.stops ?? [])
        .filter((stop) => stop.outcome === "pending")
        .map((stop) => board.findOrder(stop.orderId))
        .filter((order) => order != null)
        .map((order) => ({
            lat: order.lat,
            lng: order.lng,
            label: `#${order.id}`,
        })),
);

function reasonKey(orderId) {
    return `${openRunId.value}:${orderId}`;
}

function markFailed(orderId) {
    if (openRunId.value == null) {
        return;
    }
    board.settleStop(
        openRunId.value,
        orderId,
        "failed",
        failReasons.value[reasonKey(orderId)] ?? "",
    );
}
</script>

<template>
    <section class="space-y-4">
        <h1 class="text-xl">Транспортные листы</h1>

        <div v-if="!run" class="space-y-2">
            <p
                v-if="releasedRuns.length === 0"
                class="text-sm text-neutral-500"
            >
                Активных листов нет.
            </p>
            <button
                v-for="entry in releasedRuns"
                :key="entry.id"
                type="button"
                class="block w-full rounded border border-neutral-200 bg-white px-3 py-3 text-left text-sm"
                @click="openRunId = entry.id"
            >
                Рейс #{{ entry.id }} · остановок {{ entry.stops.length }}
            </button>
        </div>

        <div v-else class="space-y-4">
            <button
                type="button"
                class="text-sm text-neutral-600"
                @click="openRunId = null"
            >
                ← Все листы
            </button>
            <h2 class="text-lg">Рейс #{{ run.id }}</h2>
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
                                <div>#{{ stop.orderId }} {{ board.findOrder(stop.orderId)?.clientName }}</div>
                                <div class="text-xs text-neutral-500">{{ board.findOrder(stop.orderId)?.phone }}</div>
                            </td>
                            <td class="px-3 py-2">{{ board.findOrder(stop.orderId)?.address }}</td>
                            <td class="whitespace-nowrap px-3 py-2">
                                <div>{{ board.findOrder(stop.orderId)?.total }} ₽</div>
                                <div class="text-xs text-neutral-500">{{ board.findOrder(stop.orderId)?.payment }}</div>
                            </td>
                            <td class="px-3 py-2">
                                <p
                                    v-if="stop.outcome !== 'pending'"
                                    class="text-xs"
                                >
                                    {{ stop.outcome === "delivered" ? "Выдан" : `Не выдан: ${stop.reason}` }}
                                </p>
                                <div
                                    v-else
                                    class="space-y-2"
                                >
                                    <button
                                        type="button"
                                        class="rounded bg-neutral-900 px-3 py-1.5 text-white"
                                        @click="board.settleStop(run.id, stop.orderId, 'delivered', '')"
                                    >
                                        Выдан
                                    </button>
                                    <form
                                        class="flex gap-2"
                                        @submit.prevent="markFailed(stop.orderId)"
                                    >
                                        <input
                                            v-model="failReasons[reasonKey(stop.orderId)]"
                                            type="text"
                                            required
                                            placeholder="Почему не выдан"
                                            class="min-w-0 flex-1 rounded border border-neutral-300 px-2 py-1"
                                        >
                                        <button
                                            type="submit"
                                            class="rounded border border-neutral-300 px-3 py-1.5"
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
