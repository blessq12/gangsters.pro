/** Способы оплаты в UI (маппинг на Checkout BC). */

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
    return typeof id === "string" && CHECKOUT_PAYMENT_METHOD_IDS.includes(id);
}

/**
 * @param {unknown} method
 * @returns {"cash"|"card"}
 */
export function normalizeCheckoutPaymentMethod(method) {
    return isCheckoutPaymentMethod(method) ? method : "card";
}

/**
 * @param {unknown} method
 * @returns {string}
 */
export function toServerCheckoutPaymentMethod(method) {
    const normalized = normalizeCheckoutPaymentMethod(method);
    return normalized === "cash" ? "cash" : "card_courier";
}

/**
 * @param {unknown} method
 * @returns {"cash"|"card"}
 */
export function fromServerCheckoutPaymentMethod(method) {
    if (method === "cash") {
        return "cash";
    }
    if (method === "card_courier" || method === "card_online") {
        return "card";
    }
    return "card";
}
