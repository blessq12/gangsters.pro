import { defineCheckoutWizardStep } from "../defineCheckoutWizardStep";
import { CHECKOUT_WIZARD_GROUPS } from "../../checkoutWizardGroups";
import { CHECKOUT_STEP_HINTS } from "../../checkoutWizardLabels";
import CheckoutConfirmStep from "../../../../components/checkout/CheckoutConfirmStep.vue";

export const confirmCheckoutWizardStep = defineCheckoutWizardStep({
    id: "confirm",
    component: CheckoutConfirmStep,
    title: CHECKOUT_WIZARD_GROUPS.confirm,
    hint: CHECKOUT_STEP_HINTS.confirm,
});
