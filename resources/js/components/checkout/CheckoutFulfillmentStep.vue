<script setup>
import { computed, onMounted } from "vue";
import { storeToRefs } from "pinia";
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
import { useContentStore } from "../../stores/contentStore";
import { kitchenAddressLabelOrFallback } from "../../utils/system/companyDeliveryFacts";
import FormField from "../ui/FormField.vue";
import ContactsKitchenMap from "../contacts/ContactsKitchenMap.vue";
import CheckoutAddressFormFields from "./CheckoutAddressFormFields.vue";
import CheckoutAuthAddressSection from "./CheckoutAuthAddressSection.vue";
import CheckoutDeliveryZoneStatus from "./CheckoutDeliveryZoneStatus.vue";
import CheckoutInlineOptionSelect from "./CheckoutInlineOptionSelect.vue";
import CheckoutSection from "./CheckoutSection.vue";
import CheckoutStepFrame from "./CheckoutStepFrame.vue";
import CheckoutStepNav from "./CheckoutStepNav.vue";

const s = useAppDesign().components.checkout.shared;

const {
    checkoutState,
    goToFulfillmentBack,
    goToFulfillmentNext,
    setDeliveryMethod,
    setDeliveryComment,
    guestAddressDraft,
    patchGuestAddressDraft,
    scheduleDeliveryPreview,
    setPaymentMethod,
} = useCheckoutFlowContext();

const { deliveryFacts } = storeToRefs(useContentStore());

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

const isPickup = computed(
    () => checkoutIntent.deliveryInfo.method === "pickup",
);

const kitchenAddressLabel = computed(() =>
    kitchenAddressLabelOrFallback(deliveryFacts.value),
);

const paymentOptions = computed(() =>
    CHECKOUT_PAYMENT_METHOD_IDS.map((id) => {
        const meta = CHECKOUT_PAYMENT_METHOD_META[id];
        return {
            id,
            label: meta.inlineLabel,
            icon: meta.icon,
        };
    }),
);

const deliveryOptions = computed(() =>
    CHECKOUT_DELIVERY_METHOD_IDS.map((id) => {
        const meta = CHECKOUT_DELIVERY_METHOD_META[id];
        return {
            id,
            label: meta.inlineLabel,
            icon: meta.icon,
        };
    }),
);
</script>

<template>
    <CheckoutStepFrame group="fulfillment">
        <div :class="s.grid2">
            <FormField :error="paymentFieldErrors.get('method')">
                <template #default>
                    <CheckoutSection title="Оплата">
                        <CheckoutInlineOptionSelect
                            aria-label="Способ оплаты"
                            :options="paymentOptions"
                            :selected-id="checkoutIntent.paymentInfo.method"
                            @select="setPaymentMethod"
                        />
                    </CheckoutSection>
                </template>
            </FormField>

            <FormField :error="deliveryFieldErrors.get('method')">
                <template #default>
                    <CheckoutSection title="Получение">
                        <CheckoutInlineOptionSelect
                            aria-label="Способ получения заказа"
                            :options="deliveryOptions"
                            :selected-id="checkoutIntent.deliveryInfo.method"
                            @select="setDeliveryMethod"
                        />
                    </CheckoutSection>
                </template>
            </FormField>
        </div>

        <CheckoutSection
            v-if="isCourier && isGuestCheckout"
            title="Куда доставить"
            variant="form"
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

        <CheckoutSection
            v-if="isPickup"
            title="Адрес кухни"
        >
            <p :class="s.stepHint">
                {{ kitchenAddressLabel }}
            </p>
            <ContactsKitchenMap />
        </CheckoutSection>

        <CheckoutSection title="Пожелания к заказу">
            <textarea
                rows="2"
                :class="s.textareaFlow"
                placeholder="Время, упаковка, звонок перед доставкой"
                :value="checkoutIntent.deliveryInfo.comment"
                @input="setDeliveryComment($event.target.value)"
            />
        </CheckoutSection>

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
                @back="goToFulfillmentBack"
                @primary="goToFulfillmentNext"
            />
        </template>
    </CheckoutStepFrame>
</template>
