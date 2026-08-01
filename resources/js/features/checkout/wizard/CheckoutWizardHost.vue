<script setup>
import { computed, unref } from "vue";
import { useCheckoutFlowContext } from "../../../composables/checkout/checkoutFlowContext";
import { getCheckoutWizardStep } from "./checkoutWizardRegistry";

/**
 * Оболочка визарда: владеет только выбором плагина по activeStep.
 * Состояние шага и переходы — в useCheckoutWizard (через checkout flow context).
 */
const { activeStep } = useCheckoutFlowContext();

const activeStepDefinition = computed(() =>
    getCheckoutWizardStep(unref(activeStep)),
);

const ActiveStepComponent = computed(
    () => activeStepDefinition.value?.component ?? null,
);

const activeStepKey = computed(
    () => activeStepDefinition.value?.id ?? "unknown",
);
</script>

<template>
    <component
        :is="ActiveStepComponent"
        v-if="ActiveStepComponent"
        :key="activeStepKey"
    />
</template>
