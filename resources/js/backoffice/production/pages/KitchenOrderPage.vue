<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { storeToRefs } from "pinia";
import { useOpsBoard } from "../../opsBoard";

const board = useOpsBoard();
const route = useRoute();
const router = useRouter();
const { kitchenPreparing, kitchenError } = storeToRefs(board);

const now = ref(Date.now());
const finishing = ref(false);
let timer = 0;

const orderId = computed(() => Number(route.params.id));
const order = computed(() => kitchenPreparing.value.find((item) => item.id === orderId.value) ?? null);

const elapsed = computed(() => {
    const startedAt = order.value?.kitchenStartedAt;
    if (!startedAt) {
        return "00:00";
    }
    const started = Date.parse(startedAt);
    if (Number.isNaN(started)) {
        return "00:00";
    }
    const total = Math.max(0, Math.floor((now.value - started) / 1000));
    const hours = Math.floor(total / 3600);
    const minutes = Math.floor((total % 3600) / 60);
    const seconds = total % 60;
    const pad = (value) => String(value).padStart(2, "0");
    if (hours > 0) {
        return `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
    }
    return `${pad(minutes)}:${pad(seconds)}`;
});

onMounted(async () => {
    await board.loadKitchen();
    timer = window.setInterval(() => {
        now.value = Date.now();
    }, 1000);
});

onUnmounted(() => {
    window.clearInterval(timer);
});

async function finish() {
    if (!order.value || finishing.value) {
        return;
    }
    finishing.value = true;
    try {
        await board.markReady(order.value.id);
        await router.push({ name: "production-production" });
    } catch (error) {
        kitchenError.value = error instanceof Error ? error.message : "Не удалось отметить заказ готовым.";
        finishing.value = false;
    }
}
</script>

<template>
    <section class="space-y-4">
        <RouterLink
            :to="{ name: 'production-production' }"
            class="inline-flex h-9 items-center text-sm text-neutral-700"
        >
            К очереди
        </RouterLink>
        <p
            v-if="kitchenError"
            class="text-sm text-red-700"
        >
            {{ kitchenError }}
        </p>
        <p
            v-if="!order"
            class="text-sm text-neutral-500"
        >
            Заказ не в работе.
        </p>
        <div
            v-else
            class="max-w-xl rounded border border-neutral-200 bg-white p-4"
        >
            <div class="flex items-baseline justify-between gap-4">
                <h1 class="text-xl">#{{ order.id }}</h1>
                <p class="text-xl tabular-nums">{{ elapsed }}</p>
            </div>
            <ul class="mt-4 space-y-2 text-base">
                <li
                    v-for="line in order.lines"
                    :key="line.name"
                >
                    {{ line.name }} × {{ line.qty }}
                </li>
            </ul>
            <p
                v-if="order.comment"
                class="mt-4 text-sm text-neutral-500"
            >
                {{ order.comment }}
            </p>
            <button
                type="button"
                class="mt-4 inline-flex h-9 items-center rounded bg-neutral-900 px-3 text-sm text-white disabled:opacity-40"
                :disabled="finishing"
                @click="finish"
            >
                Готово
            </button>
        </div>
    </section>
</template>
