/** Семантические группы визарда оформления. */
export const CHECKOUT_WIZARD_GROUPS = {
    cart: "Корзина",
    guest: "Клиент",
    delivery: "Доставка",
    payment: "Оплата",
    confirm: "Оформление",
    success: "Готово",
};

export const CHECKOUT_WIZARD_FLOW_GUEST = Object.freeze([
    "guest",
    "delivery",
    "payment",
    "confirm",
]);

export const CHECKOUT_WIZARD_FLOW_AUTH = Object.freeze([
    "delivery",
    "payment",
    "confirm",
]);

/**
 * @param {boolean} isGuestCheckout
 * @returns {readonly ('guest'|'delivery'|'payment'|'confirm')[]}
 */
export function resolveWizardFlowSteps(isGuestCheckout) {
    return isGuestCheckout
        ? CHECKOUT_WIZARD_FLOW_GUEST
        : CHECKOUT_WIZARD_FLOW_AUTH;
}

/**
 * @param {'guest'|'delivery'|'payment'|'confirm'} step
 * @param {boolean} isGuestCheckout
 */
export function resolveWizardStepMeta(step, isGuestCheckout) {
    const flow = resolveWizardFlowSteps(isGuestCheckout);
    const index = flow.indexOf(step);
    if (index === -1) {
        return null;
    }

    const total = flow.length;

    return {
        n: index + 1,
        total,
        label: CHECKOUT_WIZARD_GROUPS[step],
    };
}
