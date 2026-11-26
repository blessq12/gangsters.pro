import { request } from "../http/transport.js";

/**
 * @param {{
 *   offset?: number,
 *   limit?: number,
 *   status?: string,
 *   q?: string,
 *   from?: string,
 *   to?: string,
 *   branch_id?: number|string,
 *   delivery_method?: string,
 * }} [query]
 */
export function listOrders(query = {}) {
    const params = new URLSearchParams();
    if (query.offset !== undefined) {
        params.set("offset", String(query.offset));
    }
    if (query.limit !== undefined) {
        params.set("limit", String(query.limit));
    }
    if (query.status) {
        params.set("status", query.status);
    }
    if (query.q) {
        params.set("q", query.q);
    }
    if (query.from) {
        params.set("from", query.from);
    }
    if (query.to) {
        params.set("to", query.to);
    }
    if (query.branch_id !== undefined && query.branch_id !== null && query.branch_id !== "") {
        params.set("branch_id", String(query.branch_id));
    }
    if (query.delivery_method) {
        params.set("delivery_method", query.delivery_method);
    }
    const qs = params.toString();
    return request("GET", `/orders${qs ? `?${qs}` : ""}`);
}

/** @param {number|string} id */
export function getOrder(id) {
    return request("GET", `/orders/${id}`);
}

/**
 * Частичная правка заказа (клиент / доставка / оплата / линии).
 * @param {number|string} id
 * @param {Record<string, unknown>} data
 */
export function updateOrder(id, data) {
    return request("PUT", `/orders/${id}`, data);
}

/**
 * Подтверждение: accept + confirm → OrderConfirmed (филиал уже на заказе).
 * @param {number|string} id
 */
export function acceptOrder(id) {
    return request("POST", `/orders/${id}/accept`, {});
}

/**
 * Отмена публикует OrderCancelled. Другие статусы этой командой не ставятся.
 * @param {number|string} id
 */
export function cancelOrder(id) {
    return request("POST", `/orders/${id}/status`, {
        target_status: "cancelled",
    });
}

/**
 * @param {number|string} id
 * @param {{ branch_id: number }} data
 */
/** @param {number|string} id */
export function completePickup(id) {
    return request("POST", `/orders/${id}/complete-pickup`);
}

/** @param {Record<string, unknown>} data */
export function quoteOrder(data) {
    return request("POST", "/orders/quote", data);
}

/** @param {Record<string, unknown>} data */
export function storeOrder(data) {
    return request("POST", "/orders", data);
}

/**
 * @param {number|string} id
 * @param {{ body: string }} data
 */
export function addOrderNote(id, data) {
    return request("POST", `/orders/${id}/notes`, data);
}
