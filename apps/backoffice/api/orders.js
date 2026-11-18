import { request } from "../http/transport.js";

/**
 * @param {{ offset?: number, limit?: number, status?: string }} [query]
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
    const qs = params.toString();
    return request("GET", `/orders${qs ? `?${qs}` : ""}`);
}

/** @param {number|string} id */
export function getOrder(id) {
    return request("GET", `/orders/${id}`);
}

/** @param {number|string} id */
export function acceptOrder(id) {
    return request("POST", `/orders/${id}/accept`);
}

/**
 * @param {number|string} id
 * @param {{ target_status: string, actor_role?: string }} data
 */
export function changeOrderStatus(id, data) {
    return request("POST", `/orders/${id}/status`, data);
}

/** @param {number|string} id */
export function completePickup(id) {
    return request("POST", `/orders/${id}/complete-pickup`);
}

/** @param {Record<string, unknown>} data */
export function storeOrder(data) {
    return request("POST", "/orders", data);
}
