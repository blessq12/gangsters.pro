<script setup>
import { computed, ref } from "vue";
import { useOrdersReadModel } from "../../features/orders/useOrdersReadModel";
import {
    formatDeliveryMethodRu,
    formatOrderDate,
    formatOrderMoneyRubles,
    formatOrderStatusRu,
    formatPaymentMethodRu,
} from "../../utils/order/orderDisplay";
import { useAppDesign } from "../../design/useAppDesign";

const oh = useAppDesign().components.client.orderHistory;

/** Во вкладке истории показываем только N последних заказов (API отдаёт свежие первыми). */
const HISTORY_TAB_LIMIT = 10;

const { orders, loading, error } = useOrdersReadModel({ autoload: true });

const ordersForTab = computed(() =>
    orders.value.slice(0, HISTORY_TAB_LIMIT),
);

const totalOrdersLoaded = computed(() => orders.value.length);

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
</script>

<template>
    <div :class="oh.root">
        <div
            v-if="loading"
            :class="oh.stateLoading"
        >
            Загружаем заказы…
        </div>

        <div
            v-else-if="error"
            :class="oh.stateError"
        >
            {{ error }}
        </div>

        <div
            v-else-if="!orders.length"
            :class="oh.stateEmpty"
        >
            Заказов пока нет. Собери корзину — и тут появится первая история.
        </div>

        <ul
            v-else
            :class="oh.list"
        >
            <li
                v-for="order in ordersForTab"
                :key="order.id"
                :class="oh.card"
            >
                <button
                    type="button"
                    :class="oh.cardHeadBtn"
                    @click="toggleExpanded(order.id)"
                >
                    <div :class="oh.cardHeadMain">
                        <p :class="oh.monoId">
                            {{ order.id }}
                        </p>
                        <p :class="oh.dateMuted">
                            {{ formatOrderDate(order.created_at) }}
                        </p>
                        <p :class="oh.statusLine">
                            {{ formatOrderStatusRu(order.status) }}
                            <span
                                v-if="order.delivery?.method"
                                :class="oh.mutedInline"
                            >
                                ·
                                {{ formatDeliveryMethodRu(order.delivery.method) }}
                            </span>
                        </p>
                    </div>
                    <div :class="oh.cardHeadAside">
                        <p :class="oh.sumStrong">
                            {{ formatOrderMoneyRubles(order.total) }}&nbsp;₽
                        </p>
                        <p :class="oh.expandHint">
                            {{ isExpanded(order.id) ? "Скрыть" : "Состав" }}
                        </p>
                    </div>
                </button>

                <div
                    v-if="isExpanded(order.id)"
                    :class="oh.cardBody"
                >
                    <ul :class="oh.itemsList">
                        <li
                            v-for="row in order.items"
                            :key="row.id"
                            :class="oh.itemRow"
                        >
                            <span :class="oh.itemName">
                                {{ row.product?.name || "Товар" }}
                                <span :class="oh.itemQtyMuted">
                                    × {{ row.quantity }}
                                </span>
                            </span>
                            <span :class="oh.itemPrice">
                                {{ formatOrderMoneyRubles(row.row_total) }}&nbsp;₽
                            </span>
                        </li>
                    </ul>
                    <p
                        v-if="order.payment?.method"
                        :class="oh.paymentFoot"
                    >
                        Оплата:
                        {{ formatPaymentMethodRu(order.payment.method) }}
                    </p>
                </div>
            </li>
        </ul>

        <p
            v-if="totalOrdersLoaded > HISTORY_TAB_LIMIT"
            :class="oh.moreHint"
        >
            Показаны {{ HISTORY_TAB_LIMIT }} последних из {{ totalOrdersLoaded }} заказов.
        </p>
    </div>
</template>
