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
export function reassignBranch(id, data) {
    return request("POST", `/orders/${id}/reassign-branch`, data);
}

/** @param {number|string} id */
export function completePickup(id) {
    return request("POST", `/orders/${id}/complete-pickup`);
}

/** @param {Record<string, unknown>} data */
export function storeOrder(data) {
    return request("POST", "/orders", data);
}
