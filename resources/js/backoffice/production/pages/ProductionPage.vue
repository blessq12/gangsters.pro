<script setup>
import { computed, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { storeToRefs } from "pinia";
import { useOpsBoard } from "../../opsBoard";

const tabs = [
    { id: "confirmed", label: "Подтверждённые" },
    { id: "preparing", label: "В работе" },
];

const tab = ref("confirmed");
const board = useOpsBoard();
const router = useRouter();
const { kitchenConfirmed, kitchenPreparing, kitchenLoaded, kitchenError } = storeToRefs(board);
const visibleOrders = computed(() => (tab.value === "confirmed" ? kitchenConfirmed.value : kitchenPreparing.value));

onMounted(() => {
    board.loadKitchen();
});

async function take(id) {
    try {
        await board.startPreparing(id);
        await router.push({ name: "production-order", params: { id: String(id) } });
    } catch (error) {
        kitchenError.value = error instanceof Error ? error.message : "Не удалось взять заказ в работу.";
    }
}

function openOrder(id) {
    router.push({ name: "production-order", params: { id: String(id) } });
}
</script>

<template>
    <section class="space-y-4">
        <h1 class="text-xl">Очередь кухни</h1>
        <div class="flex flex-wrap gap-1">
            <button
                v-for="item in tabs"
                :key="item.id"
                type="button"
                class="inline-flex h-9 items-center rounded px-3 text-sm"
                :class="tab === item.id ? 'bg-neutral-900 text-white' : 'bg-white text-neutral-700'"
                @click="tab = item.id"
            >
                {{ item.label }}
            </button>
        </div>
        <p
            v-if="kitchenError"
            class="text-sm text-red-700"
        >
            {{ kitchenError }}
        </p>
        <p
            v-if="!kitchenLoaded"
            class="text-sm text-neutral-500"
        >
            Загрузка…
        </p>
        <p
            v-else-if="visibleOrders.length === 0"
            class="text-sm text-neutral-500"
        >
            {{ tab === "confirmed" ? "Подтверждённых заказов нет." : "Заказов в работе нет." }}
        </p>
        <div
            v-else
            class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3"
        >
            <article
                v-for="order in visibleOrders"
                :key="order.id"
                class="rounded border border-neutral-200 bg-white p-4"
            >
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-base">#{{ order.id }}</h2>
                    <button
                        v-if="tab === 'confirmed'"
                        type="button"
                        class="inline-flex h-9 items-center rounded bg-neutral-900 px-3 text-sm text-white"
                        @click="take(order.id)"
                    >
                        В работу
                    </button>
                    <button
                        v-else
                        type="button"
                        class="inline-flex h-9 items-center rounded bg-neutral-900 px-3 text-sm text-white"
                        @click="openOrder(order.id)"
                    >
                        Открыть
                    </button>
                </div>
                <ul class="mt-3 space-y-1 text-sm">
                    <li
                        v-for="line in order.lines"
                        :key="`${order.id}-${line.name}`"
                    >
                        {{ line.name }} × {{ line.qty }}
                    </li>
                </ul>
            </article>
        </div>
    </section>
</template>
