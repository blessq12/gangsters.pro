import { request } from "../http/transport.js";

/**
 * @param {number|string} branchId
 * @param {{ offset?: number, limit?: number, scope?: "queued"|"active" }} [query]
 */
export function getQueue(branchId, query = {}) {
    const params = new URLSearchParams({ branch_id: String(branchId) });
    if (query.offset !== undefined) {
        params.set("offset", String(query.offset));
    }
    if (query.limit !== undefined) {
        params.set("limit", String(query.limit));
    }
    if (query.scope) {
        params.set("scope", query.scope);
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

/**
 * @param {number|string} orderId
 * @param {number|string} lineId
 */
export function markTicketLineDone(orderId, lineId) {
    return request("POST", `/production/tickets/${orderId}/lines/${lineId}/done`);
}

/**
 * @param {number|string} orderId
 * @param {number|string} lineId
 */
export function markTicketLinePacked(orderId, lineId) {
    return request("POST", `/production/tickets/${orderId}/lines/${lineId}/pack`);
}

/** @param {number|string} orderId */
export function cancelTicket(orderId) {
    return request("POST", `/production/tickets/${orderId}/cancel`);
}
