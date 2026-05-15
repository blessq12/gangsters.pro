<script setup>
import { useAppDesign } from "../../../../design/useAppDesign";
import { useCheckoutFlow } from "../../../../composables/checkout/useCheckoutFlow";
import { provideCheckoutFlow } from "../../../../composables/checkout/checkoutFlowContext";

const panels = useAppDesign().components.dockPanels;

const flow = useCheckoutFlow();
provideCheckoutFlow(flow);

const { cartStore, activeStep } = flow;
const c = panels.cart;
</script>

<template>
    <DockPanelLayout
        title="Корзина"
        description="Оформление в несколько шагов"
    >
        <template #headerActions>
            <div :class="c.headerBadge">
                {{ cartStore.cartTotalItems }} шт
            </div>
        </template>

        <CheckoutCartStep v-if="activeStep === 'cart'" />
        <CheckoutAuthStep v-else-if="activeStep === 'auth'" />
        <CheckoutDeliveryStep v-else-if="activeStep === 'delivery'" />
        <CheckoutPaymentStep v-else-if="activeStep === 'payment'" />
        <CheckoutConfirmStep v-else-if="activeStep === 'confirm'" />
        <CheckoutSuccessStep v-else-if="activeStep === 'success'" />
    </DockPanelLayout>
</template>

<style scoped></style>
