/** Семантические группы визарда оформления. */
export const CHECKOUT_WIZARD_GROUPS = {
    cart: "Корзина",
    guest: "Клиент",
    fulfillment: "Оплата и доставка",
    drinks: "Закажите напитки",
    confirm: "Оформление",
    success: "Готово",
};

export const CHECKOUT_WIZARD_FLOW_GUEST = Object.freeze([
    "guest",
    "fulfillment",
    "drinks",
    "confirm",
]);

export const CHECKOUT_WIZARD_FLOW_AUTH = Object.freeze([
    "fulfillment",
    "drinks",
    "confirm",
]);

/** Серверные suggested_step → UI-шаг. */
const SERVER_STEP_TO_UI = Object.freeze({
    delivery: "fulfillment",
    payment: "fulfillment",
});

/**
 * @param {string|null|undefined} serverStep
 * @returns {'guest'|'fulfillment'|'drinks'|'confirm'|null}
 */
export function mapServerWizardStep(serverStep) {
    if (serverStep == null || serverStep === "") {
        return null;
    }

    if (
        serverStep === "guest"
        || serverStep === "fulfillment"
        || serverStep === "drinks"
        || serverStep === "confirm"
    ) {
        return serverStep;
    }

    return SERVER_STEP_TO_UI[serverStep] ?? null;
}

/**
 * @param {boolean} isGuestCheckout
 * @param {{ includeDrinks?: boolean }} [options]
 * @returns {readonly string[]}
 */
export function resolveWizardFlowSteps(isGuestCheckout, options = {}) {
    const includeDrinks = options.includeDrinks !== false;
    const base = isGuestCheckout
        ? CHECKOUT_WIZARD_FLOW_GUEST
        : CHECKOUT_WIZARD_FLOW_AUTH;

    if (includeDrinks) {
        return base;
    }

    return Object.freeze(base.filter((step) => step !== "drinks"));
}

/**
 * @param {string} step
 * @param {boolean} isGuestCheckout
 * @param {{ includeDrinks?: boolean }} [options]
 */
export function resolveWizardStepMeta(step, isGuestCheckout, options = {}) {
    const flow = resolveWizardFlowSteps(isGuestCheckout, options);
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
