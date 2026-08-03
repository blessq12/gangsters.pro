<script setup>
import { computed, watch } from "vue";
import { useCheckout } from "../../../../features/checkout/useCheckout";
import { provideCheckoutFlow } from "../../../../composables/checkout/checkoutFlowContext";
import { resolveCheckoutDockTitle } from "../../../../features/checkout/checkoutWizardLabels";
import CheckoutWizardHost from "../../../../features/checkout/wizard/CheckoutWizardHost.vue";
import { getCheckoutWizardStep } from "../../../../features/checkout/wizard/checkoutWizardRegistry";
import { useUiStore } from "../../../../stores/uiStore";

const uiStore = useUiStore();

const flow = useCheckout();
provideCheckoutFlow(flow);

const { activeStep, handleStartCheckout } = flow;

const dockTitle = computed(
    () =>
        getCheckoutWizardStep(activeStep.value)?.title
        ?? resolveCheckoutDockTitle(activeStep.value),
);

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
        <CheckoutWizardHost />
    </DockPanelLayout>
</template>

<style scoped></style>
