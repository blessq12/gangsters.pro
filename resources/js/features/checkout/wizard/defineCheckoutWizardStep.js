/**
 * Контракт плагина шага checkout-визарда.
 *
 * Визард владеет `activeStep` и переходами.
 * Шаг — независимый UI-плагин: не знает соседей, не двигает flow сам.
 *
 * @typedef {object} CheckoutWizardStepDefinition
 * @property {string} id стабильный ключ шага
 * @property {import('vue').Component} component Vue-компонент шага
 * @property {string} title заголовок dock-панели
 * @property {string|null} [hint] подсказка под заголовком (null = скрыть)
 */

/**
 * @param {CheckoutWizardStepDefinition} definition
 * @returns {Readonly<CheckoutWizardStepDefinition>}
 */
export function defineCheckoutWizardStep(definition) {
    if (!definition || typeof definition !== "object") {
        throw new Error("defineCheckoutWizardStep: definition required");
    }
    if (!definition.id || typeof definition.id !== "string") {
        throw new Error("defineCheckoutWizardStep: id required");
    }
    if (!definition.component) {
        throw new Error(
            `defineCheckoutWizardStep: component required for "${definition.id}"`,
        );
    }
    if (!definition.title || typeof definition.title !== "string") {
        throw new Error(
            `defineCheckoutWizardStep: title required for "${definition.id}"`,
        );
    }

    return Object.freeze({
        id: definition.id,
        component: definition.component,
        title: definition.title,
        hint: definition.hint ?? null,
    });
}
