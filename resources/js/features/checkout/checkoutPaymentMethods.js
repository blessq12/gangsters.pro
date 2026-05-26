/** Способы оплаты при оформлении (согласовано с PaymentMethod::placementValues). */

export const CHECKOUT_PAYMENT_METHOD_IDS = ["cash", "card"];

export const CHECKOUT_PAYMENT_METHOD_LABELS = {
    cash: "Наличными",
    card: "Банковской картой",
};

/**
 * @param {unknown} id
 * @returns {id is string}
 */
export function isCheckoutPaymentMethod(id) {
    return (
        typeof id === "string" &&
        CHECKOUT_PAYMENT_METHOD_IDS.includes(id)
    );
}

/**
 * @param {unknown} method
 * @returns {"cash"|"card"}
 */
export function normalizeCheckoutPaymentMethod(method) {
    return isCheckoutPaymentMethod(method) ? method : "card";
}
