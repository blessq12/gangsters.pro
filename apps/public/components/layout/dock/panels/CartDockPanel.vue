<script setup>
import { computed, watch } from "vue";
import { useCheckout } from "../../../../modules/checkout/application/wizard";
import { provideCheckoutFlow } from "../../../../modules/checkout/application/flowContext";
import { resolveCheckoutDockTitle } from "../../../../modules/checkout/application/wizard";
import CheckoutWizardHost from "../../../../modules/checkout/application/CheckoutWizardHost.vue";
import { getCheckoutWizardStep } from "../../../../modules/checkout/application/wizard";
import { useUiStore } from "../../../../modules/shell/store/uiStore";

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
