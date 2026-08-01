import { defineCheckoutWizardStep } from "../defineCheckoutWizardStep";
import { CHECKOUT_WIZARD_GROUPS } from "../../checkoutWizardGroups";
import { CHECKOUT_STEP_HINTS } from "../../checkoutWizardLabels";
import CheckoutDrinksStep from "../../../../components/checkout/CheckoutDrinksStep.vue";

export const drinksCheckoutWizardStep = defineCheckoutWizardStep({
    id: "drinks",
    component: CheckoutDrinksStep,
    title: CHECKOUT_WIZARD_GROUPS.drinks,
    hint: CHECKOUT_STEP_HINTS.drinks,
});
