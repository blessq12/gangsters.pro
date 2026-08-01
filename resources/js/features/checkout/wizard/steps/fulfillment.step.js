import { defineCheckoutWizardStep } from "../defineCheckoutWizardStep";
import { CHECKOUT_WIZARD_GROUPS } from "../../checkoutWizardGroups";
import { CHECKOUT_STEP_HINTS } from "../../checkoutWizardLabels";
import CheckoutFulfillmentStep from "../../../../components/checkout/CheckoutFulfillmentStep.vue";

export const fulfillmentCheckoutWizardStep = defineCheckoutWizardStep({
    id: "fulfillment",
    component: CheckoutFulfillmentStep,
    title: CHECKOUT_WIZARD_GROUPS.fulfillment,
    hint: CHECKOUT_STEP_HINTS.fulfillment,
});
