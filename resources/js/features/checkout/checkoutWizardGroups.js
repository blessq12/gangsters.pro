/** Семантические группы визарда оформления. */
export const CHECKOUT_WIZARD_GROUPS = {
    cart: "Корзина",
    upsell: "Добавьте ещё",
    guest: "Как с тобой связаться",
    fulfillment: "Куда и как",
    confirm: "Почти готово",
    success: "Готово",
};

/** Guest: upsell → контакт → доставка/оплата → confirm */
export const CHECKOUT_WIZARD_FLOW_GUEST = Object.freeze([
    "upsell",
    "guest",
    "fulfillment",
    "confirm",
]);

/** Auth: upsell → доставка/оплата → confirm */
export const CHECKOUT_WIZARD_FLOW_AUTH = Object.freeze([
    "upsell",
    "fulfillment",
    "confirm",
]);

/** Серверные suggested_step → UI-шаг. */
const SERVER_STEP_TO_UI = Object.freeze({
    delivery: "fulfillment",
    payment: "fulfillment",
    drinks: "upsell",
});

/**
 * @param {string|null|undefined} serverStep
 * @returns {string|null}
 */
export function mapServerWizardStep(serverStep) {
    if (serverStep == null || serverStep === "") {
        return null;
    }

    if (
        serverStep === "upsell"
        || serverStep === "guest"
        || serverStep === "fulfillment"
        || serverStep === "confirm"
    ) {
        return serverStep;
    }

    if (serverStep === "drinks") {
        return "upsell";
    }

    return SERVER_STEP_TO_UI[serverStep] ?? null;
}

/**
 * @param {boolean} isGuestCheckout
 * @param {{ includeUpsell?: boolean }} [options]
 * @returns {readonly string[]}
 */
export function resolveWizardFlowSteps(isGuestCheckout, options = {}) {
    const includeUpsell = options.includeUpsell !== false;
    const base = isGuestCheckout
        ? CHECKOUT_WIZARD_FLOW_GUEST
        : CHECKOUT_WIZARD_FLOW_AUTH;

    if (includeUpsell) {
        return base;
    }

    return Object.freeze(base.filter((step) => step !== "upsell"));
}

/**
 * @param {string} step
 * @param {boolean} isGuestCheckout
 * @param {{ includeUpsell?: boolean }} [options]
 */
export function resolveWizardStepMeta(step, isGuestCheckout, options = {}) {
    const flow = resolveWizardFlowSteps(isGuestCheckout, options);
    const index = flow.indexOf(step);
    if (index === -1) {
        return null;
    }

    return {
        n: index + 1,
        total: flow.length,
        label: CHECKOUT_WIZARD_GROUPS[step],
    };
}
