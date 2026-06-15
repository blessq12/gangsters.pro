<script setup>
import { computed } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";

const chk = useAppDesign().components.checkout;
const s = chk.shared;
const su = chk.success;

const { checkoutState, goToCart } = useCheckoutFlowContext();
const { lastCreatedOrder } = checkoutState;

const orderNumber = computed(() => {
    const id = lastCreatedOrder.value?.id;
    if (id == null || id === "") {
        return null;
    }

    return String(id);
});
</script>

<template>
    <div :class="s.flowBody">
        <h2
            v-if="orderNumber"
            :class="su.orderTitle"
        >
            Заказ №{{ orderNumber }}
        </h2>
        <p
            v-else
            :class="s.stepKickerAccent"
        >
            Заказ оформлен
        </p>
        <p :class="s.textSuccessLead">
            Спасибо, бро. Мы приняли заказ и скоро свяжемся для подтверждения.
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
