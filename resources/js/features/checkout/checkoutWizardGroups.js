/** Семантические группы визарда оформления. */
export const CHECKOUT_WIZARD_GROUPS = {
    cart: "Корзина",
    guest: "Клиент",
    fulfillment: "Оплата и доставка",
    confirm: "Оформление",
    success: "Готово",
};

export const CHECKOUT_WIZARD_FLOW_GUEST = Object.freeze([
    "guest",
    "fulfillment",
    "confirm",
]);

export const CHECKOUT_WIZARD_FLOW_AUTH = Object.freeze([
    "fulfillment",
    "confirm",
]);

/** Серверные suggested_step → UI-шаг. */
const SERVER_STEP_TO_UI = Object.freeze({
    delivery: "fulfillment",
    payment: "fulfillment",
});

/**
 * @param {string|null|undefined} serverStep
 * @returns {'guest'|'fulfillment'|'confirm'|null}
 */
export function mapServerWizardStep(serverStep) {
    if (serverStep == null || serverStep === "") {
        return null;
    }

    if (
        serverStep === "guest"
        || serverStep === "fulfillment"
        || serverStep === "confirm"
    ) {
        return serverStep;
    }

    return SERVER_STEP_TO_UI[serverStep] ?? null;
}

/**
 * @param {boolean} isGuestCheckout
 * @returns {readonly ('guest'|'fulfillment'|'confirm')[]}
 */
export function resolveWizardFlowSteps(isGuestCheckout) {
    return isGuestCheckout
        ? CHECKOUT_WIZARD_FLOW_GUEST
        : CHECKOUT_WIZARD_FLOW_AUTH;
}

/**
 * @param {'guest'|'fulfillment'|'confirm'} step
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
