<script setup>
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import {
    CHECKOUT_PAYMENT_METHOD_IDS,
    CHECKOUT_PAYMENT_METHOD_LABELS,
} from "../../features/checkout/checkoutPaymentMethods";
import FormField from "../ui/FormField.vue";
import CheckoutOrderPreview from "./CheckoutOrderPreview.vue";
import CheckoutSection from "./CheckoutSection.vue";
import CheckoutStepFrame from "./CheckoutStepFrame.vue";

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
const { checkoutIntent, paymentFieldErrors } = checkoutState;
</script>

<template>
    <CheckoutStepFrame group="payment">
        <FormField :error="paymentFieldErrors.get('method')">
            <template #default>
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
            </template>
        </FormField>

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

        <CheckoutOrderPreview variant="payment" />

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
