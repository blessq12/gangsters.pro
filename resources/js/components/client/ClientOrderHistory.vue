<script setup>
import { computed, onMounted, ref } from "vue";
import { useOrderStore } from "../../stores/orderStore";
import {
    formatDeliveryMethodRu,
    formatOrderDate,
    formatOrderMoneyRubles,
    formatOrderStatusRu,
    formatPaymentMethodRu,
} from "../../utils/order/orderDisplay";

/** Во вкладке истории показываем только N последних заказов (API отдаёт свежие первыми). */
const HISTORY_TAB_LIMIT = 10;

const orderStore = useOrderStore();

const ordersForTab = computed(() =>
    orderStore.orders.slice(0, HISTORY_TAB_LIMIT),
);

const totalOrdersLoaded = computed(() => orderStore.orders.length);

const expandedIds = ref(new Set());

function toggleExpanded(orderId) {
    const next = new Set(expandedIds.value);
    if (next.has(orderId)) {
        next.delete(orderId);
    } else {
        next.add(orderId);
    }
    expandedIds.value = next;
}

function isExpanded(orderId) {
    return expandedIds.value.has(orderId);
}

onMounted(() => {
    void orderStore.fetchOrders();
});
</script>

<template>
    <div class="space-y-3 text-slate-50">
        <div
            v-if="orderStore.loading.list"
            class="rounded-2xl border border-white/10 bg-black/30 px-4 py-6 text-center text-sm text-slate-400"
        >
            Загружаем заказы…
        </div>

        <div
            v-else-if="orderStore.error.list"
            class="rounded-2xl border border-red-500/40 bg-red-950/30 px-4 py-3 text-sm text-red-200"
        >
            {{ orderStore.error.list }}
        </div>

        <div
            v-else-if="!orderStore.orders.length"
            class="rounded-2xl border border-dashed border-white/15 bg-black/25 px-4 py-6 text-center text-sm text-slate-400"
        >
            Заказов пока нет. Собери корзину — и тут появится первая история.
        </div>

        <ul
            v-else
            class="max-h-[min(28rem,55vh)] space-y-2 overflow-y-auto pr-1"
        >
            <li
                v-for="order in ordersForTab"
                :key="order.id"
                class="rounded-2xl border border-white/10 bg-black/35"
            >
                <button
                    type="button"
                    class="flex w-full items-start justify-between gap-3 px-3 py-3 text-left transition hover:bg-white/[0.04] sm:px-4"
                    @click="toggleExpanded(order.id)"
                >
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-mono text-amber-200/90">
                            {{ order.id }}
                        </p>
                        <p class="mt-0.5 text-xs text-slate-400">
                            {{ formatOrderDate(order.created_at) }}
                        </p>
                        <p class="mt-1 text-xs text-slate-300">
                            {{ formatOrderStatusRu(order.status) }}
                            <span
                                v-if="order.delivery?.method"
                                class="text-slate-500"
                            >
                                ·
                                {{ formatDeliveryMethodRu(order.delivery.method) }}
                            </span>
                        </p>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="text-sm font-semibold text-amber-300">
                            {{ formatOrderMoneyRubles(order.total) }}&nbsp;₽
                        </p>
                        <p class="text-[11px] text-slate-500">
                            {{ isExpanded(order.id) ? "Скрыть" : "Состав" }}
                        </p>
                    </div>
                </button>

                <div
                    v-if="isExpanded(order.id)"
                    class="border-t border-white/5 px-3 pb-3 pt-2 sm:px-4"
                >
                    <ul class="space-y-2 text-xs text-slate-200">
                        <li
                            v-for="row in order.items"
                            :key="row.id"
                            class="flex justify-between gap-2"
                        >
                            <span class="min-w-0 truncate">
                                {{ row.product?.name || "Товар" }}
                                <span class="text-slate-500">
                                    × {{ row.quantity }}
                                </span>
                            </span>
                            <span class="shrink-0 text-slate-300">
                                {{ formatOrderMoneyRubles(row.row_total) }}&nbsp;₽
                            </span>
                        </li>
                    </ul>
                    <p
                        v-if="order.payment?.method"
                        class="mt-3 border-t border-white/5 pt-2 text-[11px] text-slate-500"
                    >
                        Оплата:
                        {{ formatPaymentMethodRu(order.payment.method) }}
                    </p>
                </div>
            </li>
        </ul>

        <p
            v-if="totalOrdersLoaded > HISTORY_TAB_LIMIT"
            class="text-center text-[11px] text-slate-500"
        >
            Показаны {{ HISTORY_TAB_LIMIT }} последних из {{ totalOrdersLoaded }} заказов.
        </p>
    </div>
</template>
