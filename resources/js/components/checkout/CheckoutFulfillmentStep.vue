<script setup>
import { computed, onMounted } from "vue";
import { storeToRefs } from "pinia";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../modules/checkout/application/flowContext";
import { CHECKOUT_NAV_LABELS } from "../../modules/checkout/application/session";
import { useCheckoutNavTotal } from "../../modules/checkout/application/preview";
import {
    CHECKOUT_DELIVERY_COMMENT_MAX,
    CHECKOUT_DELIVERY_METHOD_IDS,
    CHECKOUT_DELIVERY_METHOD_META,
    CHECKOUT_PAYMENT_METHOD_IDS,
    CHECKOUT_PAYMENT_METHOD_META,
    CHECKOUT_PERSONS_MAX,
    CHECKOUT_PERSONS_MIN,
} from "../../modules/checkout/domain/checkoutServerMappers";
import { useContentStore } from "../../modules/content/store";
import { kitchenAddressLabelOrFallback } from "../../modules/content/application/company";
import FormField from "../ui/FormField.vue";
import ContactsKitchenMap from "../contacts/ContactsKitchenMap.vue";
import CheckoutAddressFormFields from "./CheckoutAddressFormFields.vue";
import CheckoutAuthAddressSection from "./CheckoutAuthAddressSection.vue";
import CheckoutDeliveryZoneStatus from "./CheckoutDeliveryZoneStatus.vue";
import CheckoutInlineOptionSelect from "./CheckoutInlineOptionSelect.vue";
import CheckoutSection from "./CheckoutSection.vue";
import CheckoutStepFrame from "./CheckoutStepFrame.vue";
import CheckoutStepNav from "./CheckoutStepNav.vue";
import { useOrderPreview } from "../../modules/checkout/application/preview";

const checkoutDesign = useAppDesign().components.checkout;
const s = checkoutDesign.shared;
const c = checkoutDesign.cart;

const {
    checkoutState,
    goToFulfillmentBack,
    goToFulfillmentNext,
    setDeliveryMethod,
    setDeliveryComment,
    setDeliveryPersons,
    guestAddressDraft,
    patchGuestAddressDraft,
    scheduleDeliveryPreview,
    setPaymentMethod,
} = useCheckoutFlowContext();
const { totals } = useOrderPreview();

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

const deliveryUnavailable = computed(
    () => isCourier.value && totals.value.inZone === false,
);

const personsCount = computed(() =>
    Number(checkoutIntent.deliveryInfo.persons) || CHECKOUT_PERSONS_MIN,
);

const canDecrementPersons = computed(
    () => personsCount.value > CHECKOUT_PERSONS_MIN,
);

const canIncrementPersons = computed(
    () => personsCount.value < CHECKOUT_PERSONS_MAX,
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

        <CheckoutSection title="Количество персон">
            <div :class="c.qtyBar">
                <button
                    type="button"
                    :class="c.qtyBtn"
                    :disabled="!canDecrementPersons"
                    @click="setDeliveryPersons(personsCount - 1)"
                >
                    –
                </button>
                <span :class="c.qtyLabel">
                    {{ personsCount }}
                </span>
                <button
                    type="button"
                    :class="c.qtyBtn"
                    :disabled="!canIncrementPersons"
                    @click="setDeliveryPersons(personsCount + 1)"
                >
                    +
                </button>
            </div>
        </CheckoutSection>

        <CheckoutSection title="Пожелания к заказу">
            <textarea
                rows="2"
                :class="s.textareaFlow"
                placeholder="Время, упаковка, звонок перед доставкой"
                :maxlength="CHECKOUT_DELIVERY_COMMENT_MAX"
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
                :primary-disabled="deliveryUnavailable"
                show-nav-total
                :total-label="navTotalLabel"
                @back="goToFulfillmentBack"
                @primary="goToFulfillmentNext"
            />
        </template>
    </CheckoutStepFrame>
</template>
