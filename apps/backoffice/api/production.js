import { request } from "../http/transport.js";

/**
 * @param {number|string} kitchenId
 * @param {{ offset?: number, limit?: number }} [query]
 */
export function getQueue(kitchenId, query = {}) {
    const params = new URLSearchParams({ kitchen_id: String(kitchenId) });
    if (query.offset !== undefined) {
        params.set("offset", String(query.offset));
    }
    if (query.limit !== undefined) {
        params.set("limit", String(query.limit));
    }
    return request("GET", `/production/queue?${params}`);
}

/** @param {number|string} orderId */
export function getTicket(orderId) {
    return request("GET", `/production/tickets/${orderId}`);
}

/** @param {number|string} orderId */
export function startTicket(orderId) {
    return request("POST", `/production/tickets/${orderId}/start`);
}

/** @param {number|string} orderId */
export function completeTicket(orderId) {
    return request("POST", `/production/tickets/${orderId}/complete`);
}

/** @param {number|string} orderId */
export function cancelTicket(orderId) {
    return request("POST", `/production/tickets/${orderId}/cancel`);
}
