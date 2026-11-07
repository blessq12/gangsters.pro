<script setup>
import { storeToRefs } from "pinia";
import { ref } from "vue";
import OrderJournal from "../../OrderJournal.vue";
import { useOpsBoard } from "../../opsBoard";
import CreateOrderPage from "./CreateOrderPage.vue";

const tabs = [
    { id: "journal", label: "Журнал" },
    { id: "pos", label: "Новый заказ" },
];

const tab = ref("journal");
const board = useOpsBoard();
const { ordersError, ordersReady } = storeToRefs(board);

function onCreated() {
    tab.value = "journal";
}
</script>

<template>
    <section class="space-y-4">
        <h1 class="text-xl">Заказы</h1>
        <div class="flex flex-wrap gap-1">
            <button
                v-for="item in tabs"
                :key="item.id"
                type="button"
                class="inline-flex h-9 items-center rounded px-3 text-sm"
                :class="
                    tab === item.id
                        ? 'bg-neutral-900 text-white'
                        : 'bg-white text-neutral-700'
                "
                @click="tab = item.id"
            >
                {{ item.label }}
            </button>
        </div>
        <p v-if="tab === 'journal' && ordersError" class="text-sm text-red-700">
            {{ ordersError }}
        </p>
        <p
            v-else-if="tab === 'journal' && !ordersReady"
            class="text-sm text-neutral-500"
        >
            Загрузка…
        </p>
        <OrderJournal v-if="tab === 'journal'" />
        <CreateOrderPage v-else @created="onCreated" />
    </section>
</template>
