import { defineCheckoutWizardStep } from "../defineCheckoutWizardStep";
import { CHECKOUT_WIZARD_GROUPS } from "../../checkoutWizardGroups";
import { CHECKOUT_STEP_HINTS } from "../../checkoutWizardLabels";
import CheckoutGuestStep from "../../../../components/checkout/CheckoutGuestStep.vue";

export const guestCheckoutWizardStep = defineCheckoutWizardStep({
    id: "guest",
    component: CheckoutGuestStep,
    title: CHECKOUT_WIZARD_GROUPS.guest,
    hint: CHECKOUT_STEP_HINTS.guest,
});
