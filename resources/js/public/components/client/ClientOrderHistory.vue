<script setup>
import { computed, ref } from "vue";
import { useOrdersReadModel } from "../../modules/client/application/useOrdersReadModel";
import { useRepeatOrder } from "../../modules/client/application/repeatOrder";
import {
    formatDeliveryMethodRu,
    formatOrderDate,
    formatOrderMoneyRubles,
    formatOrderStatusRu,
    formatPaymentMethodRu,
} from "../../modules/client/application/orderDisplay";
import { useAppDesign } from "../../design/useAppDesign";
import RepeatOrderCartChoiceModal from "./RepeatOrderCartChoiceModal.vue";

const oh = useAppDesign().components.client.orderHistory;

/** Во вкладке истории показываем только N последних заказов (API отдаёт свежие первыми). */
const HISTORY_TAB_LIMIT = 10;

const { orders, loading, error } = useOrdersReadModel({ autoload: true });
const {
    cartChoiceModalOpen,
    applyingRepeat,
    requestRepeatOrder,
    confirmCartChoice,
    cancelCartChoice,
    isRepeatingOrder,
} = useRepeatOrder();

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

function isPromoLine(row) {
    return row?.kind === "gift" || row?.kind === "complement";
}

function canRepeatOrder(order) {
    return order?.source !== "aggregator";
}

async function handleRepeatOrder(orderId) {
    await requestRepeatOrder(orderId);
}

async function handleMergeChoice() {
    await confirmCartChoice("merge");
}

async function handleReplaceChoice() {
    await confirmCartChoice("replace");
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
                            :key="`${row.id}-${row.kind || 'user'}`"
                            :class="oh.itemRow"
                        >
                            <span :class="oh.itemName">
                                {{ row.product?.name || "Товар" }}
                                <span
                                    v-if="isPromoLine(row)"
                                    :class="oh.promoBadge"
                                >
                                    ({{ row.kind === "gift" ? "подарок" : "комплект" }})
                                </span>
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
                    <button
                        v-if="canRepeatOrder(order)"
                        type="button"
                        :class="oh.repeatBtn"
                        :disabled="isRepeatingOrder(order.id)"
                        @click="handleRepeatOrder(order.id)"
                    >
                        {{
                            isRepeatingOrder(order.id)
                                ? "Собираем корзину…"
                                : "Повторить заказ"
                        }}
                    </button>
                </div>
            </li>
        </ul>

        <p
            v-if="totalOrdersLoaded > HISTORY_TAB_LIMIT"
            :class="oh.moreHint"
        >
            Показаны {{ HISTORY_TAB_LIMIT }} последних из {{ totalOrdersLoaded }} заказов.
        </p>

        <RepeatOrderCartChoiceModal
            v-model="cartChoiceModalOpen"
            :loading="applyingRepeat"
            @merge="handleMergeChoice"
            @replace="handleReplaceChoice"
            @cancel="cancelCartChoice"
        />
    </div>
</template>
