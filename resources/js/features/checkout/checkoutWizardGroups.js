/** Семантические группы визарда оформления. */
export const CHECKOUT_WIZARD_GROUPS = {
    cart: "Корзина",
    guest: "Клиент",
    delivery: "Доставка",
    payment: "Оплата",
    confirm: "Оформление",
};

/**
 * @param {'guest'|'delivery'|'payment'|'confirm'} step
 * @param {boolean} isGuestCheckout
 */
export function resolveWizardStepMeta(step, isGuestCheckout) {
    const total = isGuestCheckout ? 4 : 3;

    if (isGuestCheckout) {
        const map = {
            guest: { n: 1, total, label: CHECKOUT_WIZARD_GROUPS.guest },
            delivery: { n: 2, total, label: CHECKOUT_WIZARD_GROUPS.delivery },
            payment: { n: 3, total, label: CHECKOUT_WIZARD_GROUPS.payment },
            confirm: { n: 4, total, label: CHECKOUT_WIZARD_GROUPS.confirm },
        };
        return map[step] ?? null;
    }

    const map = {
        delivery: { n: 1, total, label: CHECKOUT_WIZARD_GROUPS.delivery },
        payment: { n: 2, total, label: CHECKOUT_WIZARD_GROUPS.payment },
        confirm: { n: 3, total, label: CHECKOUT_WIZARD_GROUPS.confirm },
    };
    return map[step] ?? null;
}

/**
 * @param {'guest'|'delivery'|'payment'|'confirm'|null|undefined} step
 */
export function resolveResumeCheckoutLabel(step) {
    switch (step) {
        case "guest":
            return "Продолжить";
        case "delivery":
            return "Продолжить";
        case "payment":
            return "Продолжить";
        case "confirm":
            return "Продолжить";
        default:
            return "Продолжить";
    }
}
