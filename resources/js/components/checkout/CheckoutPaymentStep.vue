<script setup>
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { CHECKOUT_NAV_LABELS } from "../../features/checkout/checkoutWizardLabels";
import { useCheckoutNavTotal } from "../../features/checkout/useCheckoutNavTotal";
import {
    CHECKOUT_PAYMENT_METHOD_IDS,
    CHECKOUT_PAYMENT_METHOD_META,
} from "../../features/checkout/checkoutPaymentMethods";
import FormField from "../ui/FormField.vue";
import CheckoutOptionCard from "./CheckoutOptionCard.vue";
import CheckoutSection from "./CheckoutSection.vue";
import CheckoutStepFrame from "./CheckoutStepFrame.vue";
import CheckoutStepNav from "./CheckoutStepNav.vue";

const s = useAppDesign().components.checkout.shared;
const p = useAppDesign().components.checkout.payment;
const o = useAppDesign().components.checkout.optionCard;

const {
    checkoutState,
    goToDelivery,
    goToConfirm,
    setPaymentMethod,
    setPaymentChangeFrom,
} = useCheckoutFlowContext();
const { checkoutIntent, paymentFieldErrors } = checkoutState;
const { navTotalLabel } = useCheckoutNavTotal();
</script>

<template>
    <CheckoutStepFrame group="payment">
        <FormField :error="paymentFieldErrors.get('method')">
            <template #default>
                <CheckoutSection title="Способ оплаты">
                    <div :class="o.listStack">
                        <CheckoutOptionCard
                            v-for="method in CHECKOUT_PAYMENT_METHOD_IDS"
                            :key="method"
                            :selected="checkoutIntent.paymentInfo.method === method"
                            :title="CHECKOUT_PAYMENT_METHOD_META[method].label"
                            :icon="CHECKOUT_PAYMENT_METHOD_META[method].icon"
                            @select="setPaymentMethod(method)"
                        />
                    </div>
                </CheckoutSection>
            </template>
        </FormField>

        <CheckoutSection
            v-if="checkoutIntent.paymentInfo.method === 'cash'"
            title="Сдача"
            variant="inset"
        >
            <div :class="p.cashExtra">
                <p :class="p.cashExtraHint">
                    Необязательно — укажи, если нужна сдача с крупной купюры.
                </p>
                <FormField label="Сумма">
                    <template #default="{ id }">
                        <input
                            :id="id"
                            type="number"
                            min="0"
                            inputmode="numeric"
                            :class="s.inputFieldFull"
                            placeholder="2000"
                            :value="checkoutIntent.paymentInfo.changeFrom ?? ''"
                            @input="setPaymentChangeFrom($event.target.value)"
                        />
                    </template>
                </FormField>
            </div>
        </CheckoutSection>

        <template #nav>
            <CheckoutStepNav
                :primary-label="CHECKOUT_NAV_LABELS.next"
                :total-label="navTotalLabel"
                @back="goToDelivery"
                @primary="goToConfirm"
            />
        </template>
    </CheckoutStepFrame>
</template>
