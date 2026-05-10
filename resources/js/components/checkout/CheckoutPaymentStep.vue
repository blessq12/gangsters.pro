<script setup>
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";

const chk = useAppDesign().components.checkout;
const s = chk.shared;
const d = chk.delivery;

const {
    checkoutState,
    goToDelivery,
    goToConfirm,
    setPaymentMethod,
    setPaymentChangeFrom,
    setCustomerComment,
} = useCheckoutFlowContext();
const { orderStore, paymentStepError } = checkoutState;
</script>

<template>
    <div :class="s.flowBody">
        <p :class="s.stepKicker">
            Шаг 2 из 3 — Оплата
        </p>

        <div class="space-y-2">
            <p :class="s.headingSm">
                Способ оплаты
            </p>
            <div :class="d.methodRow">
                <button
                    v-for="method in ['cash', 'card', 'transfer']"
                    :key="method"
                    type="button"
                    :class="[
                        s.pillRoundText,
                        orderStore.paymentInfo.method === method ? s.pillActive : s.pillInactive,
                    ]"
                    @click="setPaymentMethod(method)"
                >
                    {{
                        method === "cash"
                            ? "Наличными"
                            : method === "card"
                              ? "Банковская карта"
                              : "Перевод"
                    }}
                </button>
            </div>
        </div>

        <div
            v-if="orderStore.paymentInfo.method === 'cash'"
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
                :value="orderStore.paymentInfo.changeFrom ?? ''"
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
                :value="orderStore.customerComment"
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
