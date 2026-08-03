<script setup>
import { computed, onMounted } from "vue";
import { storeToRefs } from "pinia";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { CHECKOUT_LOADING_LABELS } from "../../features/checkout/checkoutLoadingLabels";
import { CHECKOUT_NAV_LABELS } from "../../features/checkout/checkoutWizardLabels";
import {
    CHECKOUT_DELIVERY_METHOD_META,
} from "../../features/checkout/checkoutDeliveryMethods";
import {
    formatServerDeliveryLine,
    formatServerPaymentLine,
} from "../../domain/order/checkoutServerMappers";
import { refreshOrderDraftPreview } from "../../features/checkout/checkoutSessionService";
import { useCheckoutStore } from "../../stores/checkoutStore";
import { useOrderPreview } from "../../features/checkout/useOrderPreview";
import { useCheckoutNavTotal } from "../../features/checkout/useCheckoutNavTotal";
import CheckoutOrderReview from "./CheckoutOrderReview.vue";
import CheckoutPromoStrip from "./CheckoutPromoStrip.vue";
import CheckoutSection from "./CheckoutSection.vue";
import CheckoutStepFrame from "./CheckoutStepFrame.vue";
import CheckoutStepNav from "./CheckoutStepNav.vue";
import CheckoutSummaryRow from "./CheckoutSummaryRow.vue";
import CheckoutTotalsBlock from "./CheckoutTotalsBlock.vue";

const chk = useAppDesign().components.checkout;
const s = chk.shared;
const cf = chk.confirm;
const c = chk.cart;

const {
    checkoutState,
    goToFulfillment,
    goToGuest,
    goToConfirmBack,
    handleConfirmOrder,
    confirmLoading,
    confirmError,
    canConfirmOrder,
} = useCheckoutFlowContext();

const checkoutStore = useCheckoutStore();
const { serverClient, serverDelivery, serverPayment } = storeToRefs(checkoutStore);
const { previewLoading } = useOrderPreview();
const { navTotalLabel } = useCheckoutNavTotal();

const { formatPrice, formatPhone, isGuestCheckout } = checkoutState;

const deliveryMethodLabel = computed(() => {
    const method = serverDelivery.value?.method;
    if (method === "courier" || method === "pickup") {
        return CHECKOUT_DELIVERY_METHOD_META[method]?.label ?? method;
    }
    return "—";
});

const isCourierDelivery = computed(
    () => serverDelivery.value?.method === "courier",
);

const deliveryAddressLine = computed(() => {
    if (!isCourierDelivery.value) {
        return "";
    }
    return formatServerDeliveryLine(serverDelivery.value);
});

const paymentLine = computed(() =>
    formatServerPaymentLine(serverPayment.value, formatPrice),
);

const clientName = computed(() => {
    const name = String(serverClient.value?.name || "").trim();
    return name || "—";
});

const clientPhone = computed(() => {
    const phone = serverClient.value?.phone;
    return phone ? formatPhone(String(phone)) : "—";
});

const deliveryComment = computed(() => {
    const comment = serverDelivery.value?.comment;
    return typeof comment === "string" && comment.trim() !== ""
        ? comment.trim()
        : null;
});

onMounted(() => {
    if (checkoutStore.hasCartItems) {
        void refreshOrderDraftPreview(checkoutStore).catch(() => {});
    }
});
</script>

<template>
    <CheckoutStepFrame group="confirm">
        <div :class="cf.stack">
            <p
                v-if="previewLoading"
                :class="c.previewLoading"
            >
                {{ CHECKOUT_LOADING_LABELS.orderRecalc }}
            </p>

            <CheckoutSection
                title="Состав"
                variant="form"
            >
                <CheckoutOrderReview />
            </CheckoutSection>

            <CheckoutSection
                title="Получение"
                variant="form"
            >
                <div :class="cf.summaryList">
                    <CheckoutSummaryRow
                        label="Способ"
                        :value="deliveryMethodLabel"
                        @edit="goToFulfillment"
                    />
                    <CheckoutSummaryRow
                        v-if="isCourierDelivery"
                        label="Адрес"
                        :value="deliveryAddressLine || '—'"
                        @edit="goToFulfillment"
                    />
                    <CheckoutSummaryRow
                        v-if="deliveryComment"
                        label="Пожелания"
                        :value="deliveryComment"
                        :show-edit="false"
                    />
                </div>
            </CheckoutSection>

            <CheckoutSection
                title="Оплата"
                variant="form"
            >
                <CheckoutSummaryRow
                    label="Способ"
                    :value="paymentLine"
                    @edit="goToFulfillment"
                />
            </CheckoutSection>

            <CheckoutSection
                title="Контакт"
                variant="form"
            >
                <div :class="cf.summaryList">
                    <CheckoutSummaryRow
                        label="Имя"
                        :value="clientName"
                        :show-edit="isGuestCheckout"
                        @edit="goToGuest"
                    />
                    <CheckoutSummaryRow
                        label="Телефон"
                        :value="clientPhone"
                        :show-edit="isGuestCheckout"
                        @edit="goToGuest"
                    />
                </div>
            </CheckoutSection>

            <CheckoutPromoStrip variant="confirm" />

            <CheckoutSection
                title="Сумма"
                variant="form"
            >
                <CheckoutTotalsBlock
                    depth="full"
                    surface="light"
                    :wrap-section="false"
                />
            </CheckoutSection>

            <div
                v-if="confirmError"
                :class="s.errorBanner"
            >
                {{ confirmError }}
            </div>
        </div>

        <template #nav>
            <CheckoutStepNav
                :primary-label="CHECKOUT_NAV_LABELS.confirm"
                :primary-loading="confirmLoading"
                :primary-disabled="!canConfirmOrder"
                :primary-busy-label="CHECKOUT_LOADING_LABELS.orderSubmit"
                show-nav-total
                :total-label="navTotalLabel"
                @back="goToConfirmBack"
                @primary="handleConfirmOrder"
            />
        </template>
    </CheckoutStepFrame>
</template>
