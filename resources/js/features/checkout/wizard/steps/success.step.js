import { defineCheckoutWizardStep } from "../defineCheckoutWizardStep";
import { CHECKOUT_WIZARD_GROUPS } from "../../checkoutWizardGroups";
import { CHECKOUT_STEP_HINTS } from "../../checkoutWizardLabels";
import CheckoutSuccessStep from "../../../../components/checkout/CheckoutSuccessStep.vue";

export const successCheckoutWizardStep = defineCheckoutWizardStep({
    id: "success",
    component: CheckoutSuccessStep,
    title: CHECKOUT_WIZARD_GROUPS.success,
    hint: CHECKOUT_STEP_HINTS.success,
});
