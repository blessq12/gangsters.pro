import { cartCheckoutWizardStep } from "./cart.step";
import { guestCheckoutWizardStep } from "./guest.step";
import { fulfillmentCheckoutWizardStep } from "./fulfillment.step";
import { drinksCheckoutWizardStep } from "./drinks.step";
import { confirmCheckoutWizardStep } from "./confirm.step";
import { successCheckoutWizardStep } from "./success.step";

/** Все плагины шагов (порядок регистрации не равен порядку flow). */
export const checkoutWizardStepPlugins = Object.freeze([
    cartCheckoutWizardStep,
    guestCheckoutWizardStep,
    fulfillmentCheckoutWizardStep,
    drinksCheckoutWizardStep,
    confirmCheckoutWizardStep,
    successCheckoutWizardStep,
]);
