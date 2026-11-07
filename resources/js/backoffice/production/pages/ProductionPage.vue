<script setup>
import { storeToRefs } from "pinia";
import OrderListing from "../../OrderListing.vue";
import { useOpsBoard } from "../../opsBoard";

const board = useOpsBoard();
const { kitchenQueue } = storeToRefs(board);
</script>

<template>
    <section class="space-y-4">
        <h1 class="text-xl">Очередь кухни</h1>
        <p
            v-if="kitchenQueue.length === 0"
            class="text-sm text-neutral-500"
        >
            Подтверждённых заказов нет.
        </p>
        <OrderListing
            v-else
            :orders="kitchenQueue"
        >
            <template #actions="{ order }">
                <button
                    v-if="order.status === 'confirmed'"
                    type="button"
                    class="rounded bg-neutral-900 px-3 py-1.5 text-sm text-white"
                    @click="board.startPreparing(order.id)"
                >
                    В работу
                </button>
                <button
                    v-else-if="order.status === 'preparing'"
                    type="button"
                    class="rounded bg-neutral-900 px-3 py-1.5 text-sm text-white"
                    @click="board.markReady(order.id)"
                >
                    Готово
                </button>
            </template>
        </OrderListing>
    </section>
</template>
