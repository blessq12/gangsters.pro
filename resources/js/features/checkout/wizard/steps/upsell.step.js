import { defineCheckoutWizardStep } from "../defineCheckoutWizardStep";
import { CHECKOUT_WIZARD_GROUPS } from "../../checkoutWizardGroups";
import { CHECKOUT_WAITER_LINES } from "../../checkoutWizardLabels";
import CheckoutUpsellStep from "../../../../components/checkout/CheckoutUpsellStep.vue";

export const upsellCheckoutWizardStep = defineCheckoutWizardStep({
    id: "upsell",
    component: CheckoutUpsellStep,
    title: CHECKOUT_WIZARD_GROUPS.upsell,
    hint: CHECKOUT_WAITER_LINES.upsell,
});
