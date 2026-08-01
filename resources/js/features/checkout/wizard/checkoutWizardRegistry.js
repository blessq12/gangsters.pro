import { checkoutWizardStepPlugins } from "./steps/index.js";

/** @type {Map<string, import('./defineCheckoutWizardStep').CheckoutWizardStepDefinition>} */
const stepsById = new Map();

/**
 * @param {import('./defineCheckoutWizardStep').CheckoutWizardStepDefinition} step
 */
export function registerCheckoutWizardStep(step) {
    if (!step?.id) {
        throw new Error("registerCheckoutWizardStep: step.id required");
    }
    stepsById.set(step.id, step);
}

/**
 * @param {string} id
 * @returns {import('./defineCheckoutWizardStep').CheckoutWizardStepDefinition|null}
 */
export function getCheckoutWizardStep(id) {
    if (!id) return null;
    return stepsById.get(id) ?? null;
}

/**
 * @returns {ReadonlyArray<import('./defineCheckoutWizardStep').CheckoutWizardStepDefinition>}
 */
export function listCheckoutWizardSteps() {
    return Object.freeze([...stepsById.values()]);
}

/**
 * @param {string} id
 * @returns {boolean}
 */
export function hasCheckoutWizardStep(id) {
    return stepsById.has(id);
}

for (const step of checkoutWizardStepPlugins) {
    registerCheckoutWizardStep(step);
}
