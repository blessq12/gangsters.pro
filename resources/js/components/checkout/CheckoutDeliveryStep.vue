<script setup>
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import CheckoutBenefitsPanel from "./CheckoutBenefitsPanel.vue";
import CheckoutComplementOffers from "./CheckoutComplementOffers.vue";
import CheckoutTotalsSummary from "./CheckoutTotalsSummary.vue";

const chk = useAppDesign().components.checkout;
const s = chk.shared;
const d = chk.delivery;

const {
    checkoutState,
    checkoutStepMeta,
    goToCart,
    goToGuest,
    goToPayment,
    setDeliveryMethod,
    setDeliveryComment,
    patchDeliveryAddress,
} = useCheckoutFlowContext();

const { checkoutIntent, deliveryStepError, isGuestCheckout } = checkoutState;
</script>

<template>
    <div :class="s.flowBody">
        <p :class="s.stepKicker">
            Шаг {{ checkoutStepMeta.delivery.n }} из {{ checkoutStepMeta.delivery.total }} — Доставка
        </p>

        <div class="space-y-2">
            <p :class="s.headingSm">
                Способ доставки
            </p>
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
        </div>

        <div
            v-if="checkoutIntent.deliveryInfo.method !== 'pickup' && isGuestCheckout"
            class="space-y-2"
        >
            <p :class="s.headingSm">
                Адрес курьера
            </p>
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
        </div>

        <CheckoutAuthAddressSection
            v-if="checkoutIntent.deliveryInfo.method !== 'pickup' && !isGuestCheckout"
        />

        <p
            v-if="deliveryStepError"
            :class="s.errorLine"
        >
            {{ deliveryStepError }}
        </p>

        <div :class="s.spacerAfterComment">
            <p :class="s.headingSm">
                Комментарий к доставке
            </p>
            <textarea
                rows="2"
                :class="s.textareaFlow"
                placeholder="Подъезд, этаж, код домофона и другие нюансы"
                :value="checkoutIntent.deliveryInfo.comment"
                @input="setDeliveryComment($event.target.value)"
            />
        </div>

        <CheckoutBenefitsPanel />

        <CheckoutComplementOffers />

        <CheckoutTotalsSummary />

        <div :class="s.navFooterRow">
            <button
                type="button"
                :class="s.linkUnderline"
                @click="isGuestCheckout ? goToGuest() : goToCart()"
            >
                {{ isGuestCheckout ? "Назад: контакт" : "Назад к корзине" }}
            </button>
            <button
                type="button"
                :class="s.btnPrimarySm"
                @click="goToPayment"
            >
                Далее: оплата
            </button>
        </div>
    </div>
</template>
