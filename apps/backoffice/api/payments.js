import { request } from "../http/transport.js";

/**
 * @param {{
 *   offset?: number,
 *   limit?: number,
 *   from?: string,
 *   to?: string,
 *   status?: string,
 *   method?: string,
 *   branch_id?: number|string,
 * }} [query]
 */
export function listPayments(query = {}) {
    const params = new URLSearchParams();
    if (query.offset !== undefined) {
        params.set("offset", String(query.offset));
    }
    if (query.limit !== undefined) {
        params.set("limit", String(query.limit));
    }
    if (query.from) {
        params.set("from", query.from);
    }
    if (query.to) {
        params.set("to", query.to);
    }
    if (query.status) {
        params.set("status", query.status);
    }
    if (query.method) {
        params.set("method", query.method);
    }
    if (query.branch_id !== undefined && query.branch_id !== null && query.branch_id !== "") {
        params.set("branch_id", String(query.branch_id));
    }
    const qs = params.toString();
    return request("GET", `/payments${qs ? `?${qs}` : ""}`);
}

/**
 * @param {{
 *   from?: string,
 *   to?: string,
 *   status?: string,
 *   method?: string,
 *   branch_id?: number|string,
 * }} [query]
 */
export function getPaymentSummary(query = {}) {
    const params = new URLSearchParams();
    if (query.from) {
        params.set("from", query.from);
    }
    if (query.to) {
        params.set("to", query.to);
    }
    if (query.status) {
        params.set("status", query.status);
    }
    if (query.method) {
        params.set("method", query.method);
    }
    if (query.branch_id !== undefined && query.branch_id !== null && query.branch_id !== "") {
        params.set("branch_id", String(query.branch_id));
    }
    const qs = params.toString();
    return request("GET", `/payments/summary${qs ? `?${qs}` : ""}`);
}

/** @param {number|string} id */
export function getPayment(id) {
    return request("GET", `/payments/${id}`);
}

/** @param {number|string} id */
export function markPaymentPaid(id) {
    return request("POST", `/payments/${id}/paid`);
}

/** @param {number|string} id */
export function refundPayment(id) {
    return request("POST", `/payments/${id}/refund`);
}
