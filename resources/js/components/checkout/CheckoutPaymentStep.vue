<script setup>
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import {
    CHECKOUT_PAYMENT_METHOD_IDS,
    CHECKOUT_PAYMENT_METHOD_LABELS,
} from "../../features/checkout/checkoutPaymentMethods";

const chk = useAppDesign().components.checkout;
const s = chk.shared;
const d = chk.delivery;

const {
    checkoutState,
    checkoutStepMeta,
    goToDelivery,
    goToConfirm,
    setPaymentMethod,
    setPaymentChangeFrom,
    setCustomerComment,
} = useCheckoutFlowContext();
const { checkoutIntent, paymentStepError } = checkoutState;
</script>

<template>
    <div :class="s.flowBody">
        <p :class="s.stepKicker">
            Шаг {{ checkoutStepMeta.payment.n }} из {{ checkoutStepMeta.payment.total }} — Оплата
        </p>

        <div class="space-y-2">
            <p :class="s.headingSm">
                Способ оплаты
            </p>
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
        </div>

        <div
            v-if="checkoutIntent.paymentInfo.method === 'cash'"
            class="space-y-1"
        >
            <p :class="s.headingSm">
                Сдача с
            </p>
            <input
                type="number"
                min="0"
                :class="s.textareaFlow"
                placeholder="Например, 2000"
                :value="checkoutIntent.paymentInfo.changeFrom ?? ''"
                @input="setPaymentChangeFrom($event.target.value)"
            />
        </div>

        <p
            v-if="paymentStepError"
            :class="s.errorLine"
        >
            {{ paymentStepError }}
        </p>

        <div class="space-y-1">
            <p :class="s.headingSm">
                Комментарий к заказу
            </p>
            <textarea
                rows="2"
                :class="s.textareaFlow"
                placeholder="Например: без лука, позвонить за 10 минут до доставки"
                :value="checkoutIntent.customerComment"
                @input="setCustomerComment($event.target.value)"
            />
        </div>

        <div :class="s.navFooterRow">
            <button
                type="button"
                :class="s.linkUnderline"
                @click="goToDelivery"
            >
                Назад: доставка
            </button>
            <button
                type="button"
                :class="s.btnPrimarySm"
                @click="goToConfirm"
            >
                Далее: подтвердить
            </button>
        </div>
    </div>
</template>
