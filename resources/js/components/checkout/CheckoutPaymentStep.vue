<script setup>
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import {
    CHECKOUT_PAYMENT_METHOD_IDS,
    CHECKOUT_PAYMENT_METHOD_LABELS,
} from "../../features/checkout/checkoutPaymentMethods";
import CheckoutSection from "./CheckoutSection.vue";
import CheckoutStepFrame from "./CheckoutStepFrame.vue";
import CheckoutTotalsSummary from "./CheckoutTotalsSummary.vue";

const s = useAppDesign().components.checkout.shared;
const d = useAppDesign().components.checkout.delivery;

const {
    checkoutState,
    goToDelivery,
    goToConfirm,
    setPaymentMethod,
    setPaymentChangeFrom,
    setCustomerComment,
} = useCheckoutFlowContext();
const { checkoutIntent, paymentStepError } = checkoutState;
</script>

<template>
    <CheckoutStepFrame group="payment">
        <CheckoutSection title="Способ">
            <div :class="d.methodRow">
                <button
                    v-for="method in CHECKOUT_PAYMENT_METHOD_IDS"
                    :key="method"
                    type="button"
                    :class="[
                        s.pillRoundText,
                        checkoutIntent.paymentInfo.method === method ? s.pillActive : s.pillInactive,
                    ]"
                    @click="setPaymentMethod(method)"
                >
                    {{ CHECKOUT_PAYMENT_METHOD_LABELS[method] }}
                </button>
            </div>
        </CheckoutSection>

        <CheckoutSection
            v-if="checkoutIntent.paymentInfo.method === 'cash'"
            title="Сдача с"
            variant="form"
        >
            <input
                type="number"
                min="0"
                :class="s.textareaFlow"
                placeholder="2000"
                :value="checkoutIntent.paymentInfo.changeFrom ?? ''"
                @input="setPaymentChangeFrom($event.target.value)"
            />
        </CheckoutSection>

        <CheckoutSection
            title="Комментарий"
            variant="form"
        >
            <textarea
                rows="2"
                :class="s.textareaFlow"
                placeholder="Пожелания к заказу"
                :value="checkoutIntent.customerComment"
                @input="setCustomerComment($event.target.value)"
            />
        </CheckoutSection>

        <p
            v-if="paymentStepError"
            :class="s.errorLine"
        >
            {{ paymentStepError }}
        </p>

        <CheckoutTotalsSummary />

        <template #nav>
            <button
                type="button"
                :class="s.linkUnderline"
                @click="goToDelivery"
            >
                Назад
            </button>
            <button
                type="button"
                :class="s.btnPrimarySm"
                @click="goToConfirm"
            >
                Далее
            </button>
        </template>
    </CheckoutStepFrame>
</template>
