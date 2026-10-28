<script setup>
import { computed } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../modules/checkout/application/flowContext";
import { CHECKOUT_NAV_LABELS } from "../../modules/checkout/application/session";
import CheckoutStepFrame from "./CheckoutStepFrame.vue";
import CheckoutStepNav from "./CheckoutStepNav.vue";

const s = useAppDesign().components.checkout.shared;
const su = useAppDesign().components.checkout.success;

const { goToCart, lastCreatedOrder, successSummary, checkoutState } =
    useCheckoutFlowContext();
const { formatPrice } = checkoutState;

const orderNumber = computed(() => {
    const id =
        successSummary.value?.orderId ??
        lastCreatedOrder.value?.id;
    if (id == null || id === "") {
        return null;
    }

    return String(id);
});

const recapLines = computed(() => {
    const summary = successSummary.value;
    if (!summary) {
        return [];
    }

    return [
        summary.deliveryLine ? { label: "Получение", value: summary.deliveryLine } : null,
        summary.paymentLine ? { label: "Оплата", value: summary.paymentLine } : null,
        summary.totalRubles != null
            ? { label: "Сумма", value: `${formatPrice(summary.totalRubles)} ₽` }
            : null,
    ].filter(Boolean);
});
</script>

<template>
    <CheckoutStepFrame group="success">
        <h2
            v-if="orderNumber"
            :class="su.orderTitle"
        >
            Заказ №{{ orderNumber }}
        </h2>

        <ul
            v-if="recapLines.length"
            class="space-y-2"
        >
            <li
                v-for="line in recapLines"
                :key="line.label"
                :class="s.offerCard"
            >
                <div class="min-w-0 flex-1">
                    <p :class="s.offerCardMeta">
                        {{ line.label }}
                    </p>
                    <p :class="s.offerCardTitle">
                        {{ line.value }}
                    </p>
                </div>
            </li>
        </ul>

        <p
            v-if="orderNumber"
            :class="su.supportHint"
        >
            Сохрани номер — назови его и телефон, если захочешь уточнить статус.
        </p>

        <template #nav>
            <CheckoutStepNav
                :show-back="false"
                :primary-label="CHECKOUT_NAV_LABELS.success"
                @primary="goToCart"
            />
        </template>
    </CheckoutStepFrame>
</template>
