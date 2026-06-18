<script setup>
import { computed, onMounted } from "vue";
import { storeToRefs } from "pinia";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import {
    formatServerClientLine,
    formatServerDeliveryLine,
    formatServerPaymentLine,
} from "../../features/checkout/checkoutServerMappers";
import { refreshOrderDraftPreview } from "../../features/checkout/checkoutSessionService";
import { useCheckoutStore } from "../../stores/checkoutStore";
import CheckoutOrderPreview from "./CheckoutOrderPreview.vue";
import CheckoutOrderReview from "./CheckoutOrderReview.vue";
import CheckoutSection from "./CheckoutSection.vue";
import CheckoutStepFrame from "./CheckoutStepFrame.vue";

const chk = useAppDesign().components.checkout;
const s = chk.shared;
const cf = chk.confirm;

const {
    checkoutState,
    goToPayment,
    handleConfirmOrder,
    confirmLoading,
    confirmError,
    giftSelectionRequired,
    canConfirmOrder,
} = useCheckoutFlowContext();

const checkoutStore = useCheckoutStore();
const { serverClient, serverDelivery, serverPayment } = storeToRefs(checkoutStore);

const { formatPrice, formatPhone } = checkoutState;

const deliveryAddressLine = computed(() =>
    formatServerDeliveryLine(serverDelivery.value),
);

const paymentLine = computed(() =>
    formatServerPaymentLine(serverPayment.value, formatPrice),
);

const clientLine = computed(() =>
    formatServerClientLine(serverClient.value, formatPhone),
);

const clientEmail = computed(() => {
    const email = serverClient.value?.email;
    return typeof email === "string" && email.trim() !== "" ? email.trim() : null;
});

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
        <CheckoutOrderPreview
            variant="confirm"
            part="benefits"
        />

        <CheckoutOrderPreview
            variant="confirm"
            part="gift"
        />

        <CheckoutSection title="Заказ">
            <div :class="cf.summaryCard">
                <CheckoutOrderReview />
                <CheckoutOrderPreview
                    variant="confirm"
                    part="totals"
                />
            </div>
        </CheckoutSection>

        <div :class="cf.metaRow">
            <CheckoutSection
                title="Доставка · оплата"
                variant="muted"
            >
                <p :class="s.textBodyXs">
                    {{ deliveryAddressLine }}
                </p>
                <p :class="s.textBodyXs">
                    {{ paymentLine }}
                </p>
                <p
                    v-if="deliveryComment"
                    :class="s.textMutedLine"
                >
                    {{ deliveryComment }}
                </p>
            </CheckoutSection>

            <CheckoutSection
                title="Клиент"
                variant="muted"
            >
                <p :class="s.textBodyXs">
                    {{ clientLine }}
                </p>
                <p
                    v-if="clientEmail"
                    :class="s.textMutedLine"
                >
                    {{ clientEmail }}
                </p>
            </CheckoutSection>
        </div>

        <div
            v-if="giftSelectionRequired"
            :class="s.errorBanner"
        >
            Выбери подарок, чтобы подтвердить заказ.
        </div>

        <div
            v-if="confirmError"
            :class="s.errorBanner"
        >
            {{ confirmError }}
        </div>

        <template #nav>
            <button
                type="button"
                :class="s.linkUnderline"
                @click="goToPayment"
            >
                Назад
            </button>
            <button
                type="button"
                :class="s.btnPrimarySmBusy"
                :disabled="confirmLoading || !canConfirmOrder"
                @click="handleConfirmOrder"
            >
                <span v-if="confirmLoading">
                    Отправляем…
                </span>
                <span v-else>
                    Подтвердить
                </span>
            </button>
        </template>
    </CheckoutStepFrame>
</template>
