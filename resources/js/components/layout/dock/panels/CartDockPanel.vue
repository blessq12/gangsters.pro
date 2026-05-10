<script setup>
import { useAppDesign } from "../../../../design/useAppDesign";
import { useCheckoutFlow } from "../../../../composables/checkout/useCheckoutFlow";
import { provideCheckoutFlow } from "../../../../composables/checkout/checkoutFlowContext";

const panels = useAppDesign().components.dockPanels;

const flow = useCheckoutFlow();
provideCheckoutFlow(flow);

const { cartStore, activeStep } = flow;
const s = panels.shared;
const c = panels.cart;
</script>

<template>
    <div :class="s.shell">
        <div :class="s.stackCart">
            <div :class="s.headerRowFlex">
                <p :class="s.typography.panelTitle">
                    Корзина
                </p>
                <div :class="c.headerBadge">
                    {{ cartStore.cartTotalItems }} шт
                </div>
            </div>

            <CheckoutCartStep v-if="activeStep === 'cart'" />
            <CheckoutAuthStep v-else-if="activeStep === 'auth'" />
            <CheckoutDeliveryStep v-else-if="activeStep === 'delivery'" />
            <CheckoutPaymentStep v-else-if="activeStep === 'payment'" />
            <CheckoutConfirmStep v-else-if="activeStep === 'confirm'" />
            <CheckoutSuccessStep v-else-if="activeStep === 'success'" />
        </div>
    </div>
</template>

<style scoped></style>
