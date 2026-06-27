<script setup>
import { storeToRefs } from "pinia";
import { computed, onMounted } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { CHECKOUT_NAV_LABELS } from "../../features/checkout/checkoutWizardLabels";
import { useCheckoutNavTotal } from "../../features/checkout/useCheckoutNavTotal";
import {
    CHECKOUT_DELIVERY_METHOD_IDS,
    CHECKOUT_DELIVERY_METHOD_META,
} from "../../features/checkout/checkoutDeliveryMethods";
import {
    CHECKOUT_PAYMENT_METHOD_IDS,
    CHECKOUT_PAYMENT_METHOD_META,
} from "../../features/checkout/checkoutPaymentMethods";
import FormField from "../ui/FormField.vue";
import CheckoutAddressFormFields from "./CheckoutAddressFormFields.vue";
import CheckoutAuthAddressSection from "./CheckoutAuthAddressSection.vue";
import CheckoutCollapsibleSection from "./CheckoutCollapsibleSection.vue";
import CheckoutDeliveryZoneStatus from "./CheckoutDeliveryZoneStatus.vue";
import CheckoutInlineOptionSelect from "./CheckoutInlineOptionSelect.vue";
import CheckoutSection from "./CheckoutSection.vue";
import CheckoutStepFrame from "./CheckoutStepFrame.vue";
import CheckoutStepNav from "./CheckoutStepNav.vue";

const s = useAppDesign().components.checkout.shared;
const p = useAppDesign().components.checkout.payment;

const {
    checkoutState,
    goToCart,
    goToGuest,
    goToFulfillmentNext,
    setDeliveryMethod,
    setDeliveryComment,
    guestAddressDraft,
    patchGuestAddressDraft,
    scheduleDeliveryPreview,
    setPaymentMethod,
    setPaymentChangeFrom,
} = useCheckoutFlowContext();

onMounted(() => {
    scheduleDeliveryPreview();
});

const {
    checkoutIntent,
    deliveryFieldErrors,
    paymentFieldErrors,
    isGuestCheckout,
} = checkoutState;
const { flushing } = storeToRefs(checkoutIntent);
const { navTotalLabel } = useCheckoutNavTotal();

const isCourier = computed(
    () => checkoutIntent.deliveryInfo.method === "courier",
);

const paymentOptions = computed(() =>
    CHECKOUT_PAYMENT_METHOD_IDS.map((id) => ({
        id,
        label: CHECKOUT_PAYMENT_METHOD_META[id].inlineLabel,
    })),
);

const deliveryOptions = computed(() =>
    CHECKOUT_DELIVERY_METHOD_IDS.map((id) => ({
        id,
        label: CHECKOUT_DELIVERY_METHOD_META[id].inlineLabel,
    })),
);
</script>

<template>
    <CheckoutStepFrame group="fulfillment">
        <FormField :error="paymentFieldErrors.get('method')">
            <template #default>
                <CheckoutSection title="Способ оплаты">
                    <CheckoutInlineOptionSelect
                        aria-label="Способ оплаты"
                        :options="paymentOptions"
                        :selected-id="checkoutIntent.paymentInfo.method"
                        @select="setPaymentMethod"
                    />
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

        <FormField :error="deliveryFieldErrors.get('method')">
            <template #default>
                <CheckoutSection title="Как получить заказ">
                    <CheckoutInlineOptionSelect
                        aria-label="Способ получения заказа"
                        :options="deliveryOptions"
                        :selected-id="checkoutIntent.deliveryInfo.method"
                        @select="setDeliveryMethod"
                    />
                </CheckoutSection>
            </template>
        </FormField>

        <CheckoutSection
            v-if="isCourier && isGuestCheckout"
            title="Куда доставить"
            variant="inset"
        >
            <CheckoutAddressFormFields
                :street="guestAddressDraft.street"
                :house="guestAddressDraft.house"
                :entrance="guestAddressDraft.entrance"
                :apartment="guestAddressDraft.apartment"
                :street-error="deliveryFieldErrors.get('street')"
                :house-error="deliveryFieldErrors.get('house')"
                @update:street="patchGuestAddressDraft({ street: $event })"
                @update:house="patchGuestAddressDraft({ house: $event })"
                @update:entrance="patchGuestAddressDraft({ entrance: $event })"
                @update:apartment="patchGuestAddressDraft({ apartment: $event })"
            />
        </CheckoutSection>

        <CheckoutAuthAddressSection
            v-if="isCourier && !isGuestCheckout"
        />

        <CheckoutDeliveryZoneStatus v-if="isCourier" />

        <CheckoutCollapsibleSection
            v-if="isCourier"
            title="Пожелания к заказу"
        >
            <textarea
                rows="2"
                :class="s.textareaFlow"
                placeholder="Время, упаковка, звонок перед доставкой"
                :value="checkoutIntent.deliveryInfo.comment"
                @input="setDeliveryComment($event.target.value)"
            />
        </CheckoutCollapsibleSection>

        <p
            v-if="deliveryFieldErrors.formError"
            :class="s.errorLine"
        >
            {{ deliveryFieldErrors.formError }}
        </p>

        <template #nav>
            <CheckoutStepNav
                :primary-label="CHECKOUT_NAV_LABELS.next"
                :primary-loading="flushing"
                show-nav-total
                :total-label="navTotalLabel"
                @back="isGuestCheckout ? goToGuest() : goToCart()"
                @primary="goToFulfillmentNext"
            />
        </template>
    </CheckoutStepFrame>
</template>
