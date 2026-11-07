<script setup>
import { computed } from "vue";
import { storeToRefs } from "pinia";
import StopsMap from "../../StopsMap.vue";
import OrderListing from "../../OrderListing.vue";
import { useOpsBoard } from "../../opsBoard";

const board = useOpsBoard();
const { courierPool, draft, releasedRuns, ordersError } = storeToRefs(board);

const draftPoints = computed(() =>
    (draft.value?.stops ?? [])
        .map((stop) => board.findOrder(stop.orderId))
        .filter((order) => order != null)
        .map((order) => ({
            lat: order.lat,
            lng: order.lng,
            label: `#${order.id}`,
        })),
);
</script>

<template>
    <section class="space-y-6">
        <h1 class="text-xl">Логистика</h1>
        <p
            v-if="ordersError"
            class="text-sm text-red-700"
        >
            {{ ordersError }}
        </p>

        <div class="space-y-3">
            <h2 class="text-sm font-medium">Пул готовых</h2>
            <p
                v-if="courierPool.length === 0"
                class="text-sm text-neutral-500"
            >
                Готовых курьерских заказов нет.
            </p>
            <OrderListing
                v-else
                :orders="courierPool"
            >
                <template #actions="{ order }">
                    <button
                        type="button"
                        class="rounded bg-neutral-900 px-3 py-1.5 text-sm text-white"
                        @click="board.addToDraft(order.id)"
                    >
                        В лист
                    </button>
                </template>
            </OrderListing>
        </div>

        <div class="space-y-3">
            <h2 class="text-sm font-medium">Выпущенные листы</h2>
            <p
                v-if="releasedRuns.length === 0"
                class="text-sm text-neutral-500"
            >
                Активных листов нет.
            </p>
            <article
                v-for="run in releasedRuns"
                :key="run.id"
                class="rounded border border-neutral-200 bg-white p-3 text-sm"
            >
                <p class="font-medium">Рейс #{{ run.id }}</p>
                <ol class="mt-2 list-decimal space-y-1 pl-4">
                    <li
                        v-for="stop in run.stops"
                        :key="stop.orderId"
                    >
                        #{{ stop.orderId }}
                        {{ board.findOrder(stop.orderId)?.address }}
                        · {{ stop.outcome === "pending" ? "в пути" : stop.outcome }}
                    </li>
                </ol>
            </article>
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-sm font-medium">
                    {{ draft ? `Черновик рейс #${draft.id}` : "Черновик" }}
                </h2>
                <button
                    type="button"
                    class="rounded bg-neutral-900 px-3 py-1.5 text-sm text-white disabled:opacity-40"
                    :disabled="!draft || draft.stops.length === 0"
                    @click="board.releaseDraft()"
                >
                    Выпустить
                </button>
            </div>
            <p
                v-if="!draft || draft.stops.length === 0"
                class="text-sm text-neutral-500"
            >
                Добавь заказы из пула и выстрой порядок остановок.
            </p>
            <div
                v-else
                class="overflow-x-auto rounded border border-neutral-200 bg-white"
            >
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-neutral-200 bg-neutral-50 text-xs text-neutral-500">
                        <tr>
                            <th class="px-3 py-2 font-medium">#</th>
                            <th class="px-3 py-2 font-medium">Заказ</th>
                            <th class="px-3 py-2 font-medium">Адрес</th>
                            <th class="px-3 py-2 font-medium" />
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(stop, index) in draft.stops"
                            :key="stop.orderId"
                            class="border-b border-neutral-100 last:border-0"
                        >
                            <td class="px-3 py-2">{{ index + 1 }}</td>
                            <td class="px-3 py-2">#{{ stop.orderId }}</td>
                            <td class="px-3 py-2">{{ board.findOrder(stop.orderId)?.address }}</td>
                            <td class="px-3 py-2">
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        class="rounded border border-neutral-300 px-2 py-1"
                                        @click="board.moveStop(stop.orderId, -1)"
                                    >
                                        Выше
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded border border-neutral-300 px-2 py-1"
                                        @click="board.moveStop(stop.orderId, 1)"
                                    >
                                        Ниже
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded border border-neutral-300 px-2 py-1"
                                        @click="board.removeFromDraft(stop.orderId)"
                                    >
                                        Убрать
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <StopsMap :points="draftPoints" />
        </div>
    </section>
</template>
