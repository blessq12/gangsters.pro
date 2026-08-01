<script setup>
import { computed, onMounted } from "vue";
import { storeToRefs } from "pinia";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { CHECKOUT_LOADING_LABELS } from "../../features/checkout/checkoutLoadingLabels";
import { CHECKOUT_NAV_LABELS } from "../../features/checkout/checkoutWizardLabels";
import {
    formatServerClientLine,
    formatServerDeliveryLine,
    formatServerPaymentLine,
} from "../../features/checkout/checkoutServerMappers";
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

const deliveryAddressLine = computed(() =>
    formatServerDeliveryLine(serverDelivery.value),
);

const paymentLine = computed(() =>
    formatServerPaymentLine(serverPayment.value, formatPrice),
);

const clientLine = computed(() =>
    formatServerClientLine(serverClient.value, formatPhone),
);

const deliveryComment = computed(() => {
    const comment = serverDelivery.value?.comment;
    return typeof comment === "string" && comment.trim() !== "" ? comment.trim() : null;
});

onMounted(() => {
    if (checkoutStore.hasCartItems) {
        void refreshOrderDraftPreview(checkoutStore).catch(() => {});
    }
});
</script>

<template>
    <CheckoutStepFrame group="confirm">
        <p
            v-if="previewLoading"
            :class="c.previewLoading"
        >
            {{ CHECKOUT_LOADING_LABELS.orderRecalc }}
        </p>

        <CheckoutSection title="Заказ">
            <div :class="cf.summaryCard">
                <CheckoutOrderReview />
                <CheckoutTotalsBlock
                    depth="full"
                    :wrap-section="false"
                />
            </div>
        </CheckoutSection>

        <CheckoutSection
            title="Проверь данные"
            variant="inset"
        >
            <div :class="cf.summaryList">
                <CheckoutSummaryRow
                    label="Контакт"
                    :value="clientLine"
                    :show-edit="isGuestCheckout"
                    @edit="goToGuest"
                />
                <CheckoutSummaryRow
                    label="Оплата"
                    :value="paymentLine"
                    @edit="goToFulfillment"
                />
                <CheckoutSummaryRow
                    label="Получение"
                    :value="deliveryAddressLine"
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

        <CheckoutPromoStrip variant="confirm" />

        <div
            v-if="confirmError"
            :class="s.errorBanner"
        >
            {{ confirmError }}
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
