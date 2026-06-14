<script setup>
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";

const chk = useAppDesign().components.checkout;
const s = chk.shared;
const su = chk.success;

const { checkoutState, goToCart } = useCheckoutFlowContext();
const { orderStore } = checkoutState;
</script>

<template>
    <div :class="s.flowBody">
        <p :class="s.stepKickerAccent">
            Заказ оформлен
        </p>
        <p :class="s.textSuccessLead">
            Спасибо, бро. Мы приняли заказ и скоро свяжемся для подтверждения.
        </p>
        <p :class="s.textSuccessBody">
            Номер оформления:
            <span :class="s.monoAccent">
                {{ checkoutState.checkoutStore?.checkoutId ?? orderStore.lastCreatedOrder?.id ?? "—" }}
            </span>
        </p>
        <div :class="su.footerActions">
            <button
                type="button"
                :class="s.btnPrimaryMd"
                @click="goToCart"
            >
                Вернуться к меню
            </button>
        </div>
    </div>
</template>
