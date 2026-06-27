<script setup>
import { computed, watch } from "vue";
import { useAppDesign } from "../../../../design/useAppDesign";
import { useCheckoutFlow } from "../../../../composables/checkout/useCheckoutFlow";
import { provideCheckoutFlow } from "../../../../composables/checkout/checkoutFlowContext";
import { resolveCheckoutDockTitle } from "../../../../features/checkout/checkoutWizardLabels";
import { useUiStore } from "../../../../stores/uiStore";

const panels = useAppDesign().components.dockPanels;
const uiStore = useUiStore();

const flow = useCheckoutFlow();
provideCheckoutFlow(flow);

const { cartStore, activeStep, handleStartCheckout } = flow;
const c = panels.cart;

const dockTitle = computed(() => resolveCheckoutDockTitle(activeStep.value));

function tryConsumeCheckoutStart() {
    if (!uiStore.pendingCheckoutStart) return;
    if (uiStore.dockActiveId !== "cart") return;
    uiStore.consumeCheckoutStart();
    handleStartCheckout();
}

watch(
    () => uiStore.pendingCheckoutStart,
    (pending) => {
        if (pending) tryConsumeCheckoutStart();
    },
);

watch(
    () => uiStore.dockActiveId,
    () => {
        tryConsumeCheckoutStart();
    },
);
</script>

<template>
    <DockPanelLayout :title="dockTitle">
        <template #headerActions>
            <div :class="c.headerBadge">
                {{ cartStore.cartTotalItems }} шт
            </div>
        </template>

        <CheckoutCartStep v-if="activeStep === 'cart'" />
        <CheckoutGuestStep v-else-if="activeStep === 'guest'" />
        <CheckoutFulfillmentStep v-else-if="activeStep === 'fulfillment'" />
        <CheckoutConfirmStep v-else-if="activeStep === 'confirm'" />
        <CheckoutSuccessStep v-else-if="activeStep === 'success'" />
    </DockPanelLayout>
</template>

<style scoped></style>
