<script setup>
import { computed } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { CHECKOUT_NAV_LABELS } from "../../features/checkout/checkoutWizardLabels";
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
    <div :class="[s.flowBody, 'space-y-4']">
        <h2
            v-if="orderNumber"
            :class="su.orderTitle"
        >
            Заказ №{{ orderNumber }}
        </h2>
        <p
            v-else
            :class="s.stepKickerAccent"
        >
            Заказ оформлен
        </p>

        <p :class="s.textSuccessLead">
            Приняли заказ — скоро позвоним для подтверждения.
        </p>

        <dl
            v-if="recapLines.length"
            class="space-y-2"
        >
            <div
                v-for="line in recapLines"
                :key="line.label"
                class="flex items-baseline justify-between gap-3 text-xs"
            >
                <dt class="text-app-muted">
                    {{ line.label }}
                </dt>
                <dd class="text-right text-app-canvas-fg">
                    {{ line.value }}
                </dd>
            </div>
        </dl>

        <p
            v-if="orderNumber"
            :class="su.supportHint"
        >
            Сохрани номер — назови его и телефон, если захочешь уточнить статус.
        </p>

        <CheckoutStepNav
            :show-back="false"
            :primary-label="CHECKOUT_NAV_LABELS.success"
            @primary="goToCart"
        />
    </div>
</template>
