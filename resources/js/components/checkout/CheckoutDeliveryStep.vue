<script setup>
import { storeToRefs } from "pinia";
import { computed, onMounted } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { CHECKOUT_LOADING_LABELS } from "../../features/checkout/checkoutLoadingLabels";
import { CHECKOUT_NAV_LABELS } from "../../features/checkout/checkoutWizardLabels";
import { useCheckoutNavTotal } from "../../features/checkout/useCheckoutNavTotal";
import {
    CHECKOUT_DELIVERY_METHOD_IDS,
    CHECKOUT_DELIVERY_METHOD_META,
} from "../../features/checkout/checkoutDeliveryMethods";
import FormField from "../ui/FormField.vue";
import CheckoutAddressFormFields from "./CheckoutAddressFormFields.vue";
import CheckoutAuthAddressSection from "./CheckoutAuthAddressSection.vue";
import CheckoutCollapsibleSection from "./CheckoutCollapsibleSection.vue";
import CheckoutDeliveryZoneStatus from "./CheckoutDeliveryZoneStatus.vue";
import CheckoutOptionCard from "./CheckoutOptionCard.vue";
import CheckoutSection from "./CheckoutSection.vue";
import CheckoutStepFrame from "./CheckoutStepFrame.vue";
import CheckoutStepNav from "./CheckoutStepNav.vue";

const s = useAppDesign().components.checkout.shared;
const o = useAppDesign().components.checkout.optionCard;

const {
    checkoutState,
    goToCart,
    goToGuest,
    goToPayment,
    setDeliveryMethod,
    setDeliveryComment,
    guestAddressDraft,
    patchGuestAddressDraft,
    scheduleDeliveryPreview,
} = useCheckoutFlowContext();

onMounted(() => {
    scheduleDeliveryPreview();
});

const { checkoutIntent, deliveryFieldErrors, isGuestCheckout } = checkoutState;
const { flushing } = storeToRefs(checkoutIntent);
const { navTotalLabel } = useCheckoutNavTotal();

const isCourier = computed(
    () => checkoutIntent.deliveryInfo.method === "courier",
);
</script>

<template>
    <CheckoutStepFrame group="delivery">
        <FormField :error="deliveryFieldErrors.get('method')">
            <template #default>
                <CheckoutSection title="Как получить заказ">
                    <div :class="o.listStack">
                        <CheckoutOptionCard
                            v-for="method in CHECKOUT_DELIVERY_METHOD_IDS"
                            :key="method"
                            :selected="checkoutIntent.deliveryInfo.method === method"
                            :title="CHECKOUT_DELIVERY_METHOD_META[method].label"
                            :icon="CHECKOUT_DELIVERY_METHOD_META[method].icon"
                            @select="setDeliveryMethod(method)"
                        />
                    </div>
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
                :primary-busy-label="CHECKOUT_LOADING_LABELS.zoneCheck"
                :total-label="navTotalLabel"
                @back="isGuestCheckout ? goToGuest() : goToCart()"
                @primary="goToPayment"
            />
        </template>
    </CheckoutStepFrame>
</template>
