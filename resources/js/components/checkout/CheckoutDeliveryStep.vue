<script setup>
import { onMounted } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import CheckoutAuthAddressSection from "./CheckoutAuthAddressSection.vue";
import CheckoutOrderPreview from "./CheckoutOrderPreview.vue";
import CheckoutSection from "./CheckoutSection.vue";
import CheckoutStepFrame from "./CheckoutStepFrame.vue";

const s = useAppDesign().components.checkout.shared;
const d = useAppDesign().components.checkout.delivery;

const {
    checkoutState,
    goToCart,
    goToGuest,
    goToPayment,
    setDeliveryMethod,
    setDeliveryComment,
    patchDeliveryAddress,
    scheduleDeliveryPreview,
} = useCheckoutFlowContext();

onMounted(() => {
    scheduleDeliveryPreview();
});

const { checkoutIntent, deliveryStepError, isGuestCheckout } = checkoutState;
</script>

<template>
    <CheckoutStepFrame group="delivery">
        <CheckoutSection title="Способ">
            <div :class="d.methodRow">
                <button
                    v-for="method in ['courier', 'pickup']"
                    :key="method"
                    type="button"
                    :class="[
                        s.pillRoundText,
                        checkoutIntent.deliveryInfo.method === method ? s.pillActive : s.pillInactive,
                    ]"
                    @click="setDeliveryMethod(method)"
                >
                    {{ method === "courier" ? "Курьер" : "Самовывоз" }}
                </button>
            </div>
        </CheckoutSection>

        <CheckoutSection
            v-if="checkoutIntent.deliveryInfo.method !== 'pickup' && isGuestCheckout"
            title="Адрес"
            variant="form"
        >
            <div :class="s.grid2">
                <input
                    :value="checkoutIntent.deliveryInfo.address?.street ?? ''"
                    type="text"
                    placeholder="Улица"
                    :class="s.inputFieldCol2"
                    @input="
                        patchDeliveryAddress({
                            street: $event.target.value,
                        })
                    "
                />
                <input
                    :value="checkoutIntent.deliveryInfo.address?.house ?? ''"
                    type="text"
                    placeholder="Дом"
                    :class="s.inputFieldGridCell"
                    @input="
                        patchDeliveryAddress({
                            house: $event.target.value,
                        })
                    "
                />
                <input
                    :value="checkoutIntent.deliveryInfo.address?.entrance ?? ''"
                    type="text"
                    placeholder="Подъезд"
                    :class="s.inputFieldGridCell"
                    @input="
                        patchDeliveryAddress({
                            entrance: $event.target.value,
                        })
                    "
                />
                <input
                    :value="checkoutIntent.deliveryInfo.address?.apartment ?? ''"
                    type="text"
                    placeholder="Квартира"
                    :class="s.inputFieldCol2"
                    @input="
                        patchDeliveryAddress({
                            apartment: $event.target.value,
                        })
                    "
                />
            </div>
        </CheckoutSection>

        <CheckoutAuthAddressSection
            v-if="checkoutIntent.deliveryInfo.method !== 'pickup' && !isGuestCheckout"
        />

        <CheckoutSection
            title="Комментарий"
            variant="form"
        >
            <textarea
                rows="2"
                :class="s.textareaFlow"
                placeholder="Подъезд, код, этаж"
                :value="checkoutIntent.deliveryInfo.comment"
                @input="setDeliveryComment($event.target.value)"
            />
        </CheckoutSection>

        <p
            v-if="deliveryStepError"
            :class="s.errorLine"
        >
            {{ deliveryStepError }}
        </p>

        <CheckoutOrderPreview variant="delivery" />

        <template #nav>
            <button
                type="button"
                :class="s.linkUnderline"
                @click="isGuestCheckout ? goToGuest() : goToCart()"
            >
                Назад
            </button>
            <button
                type="button"
                :class="s.btnPrimarySm"
                @click="goToPayment"
            >
                Далее
            </button>
        </template>
    </CheckoutStepFrame>
</template>
