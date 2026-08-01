import { defineCheckoutWizardStep } from "../defineCheckoutWizardStep";
import { CHECKOUT_WIZARD_GROUPS } from "../../checkoutWizardGroups";
import { CHECKOUT_STEP_HINTS } from "../../checkoutWizardLabels";
import CheckoutCartStep from "../../../../components/checkout/CheckoutCartStep.vue";

export const cartCheckoutWizardStep = defineCheckoutWizardStep({
    id: "cart",
    component: CheckoutCartStep,
    title: CHECKOUT_WIZARD_GROUPS.cart,
    hint: CHECKOUT_STEP_HINTS.cart,
});
