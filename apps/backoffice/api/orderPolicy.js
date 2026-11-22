import { request } from "../http/transport.js";

/** @param {number|string} branchId */
export function getOrderPolicy(branchId) {
    return request("GET", `/order/policy?branch_id=${encodeURIComponent(String(branchId))}`);
}

/**
 * @param {string} path
 * @param {{ offset?: number, limit?: number, branch_id?: number|string }} [query]
 */
function withPage(path, query = {}) {
    const params = new URLSearchParams();
    if (query.branch_id !== undefined) {
        params.set("branch_id", String(query.branch_id));
    }
    if (query.offset !== undefined) {
        params.set("offset", String(query.offset));
    }
    if (query.limit !== undefined) {
        params.set("limit", String(query.limit));
    }
    const qs = params.toString();
    return request("GET", `${path}${qs ? `?${qs}` : ""}`);
}

/** @param {number|string} branchId @param {{ offset?: number, limit?: number }} [query] */
export function listOrderPolicyRoles(branchId, query = {}) {
    return withPage("/order/policy/roles", { ...query, branch_id: branchId });
}

/** @param {number|string} branchId @param {{ offset?: number, limit?: number }} [query] */
export function listOrderPolicyZones(branchId, query = {}) {
    return withPage("/order/policy/zones", { ...query, branch_id: branchId });
}

/** @param {Record<string, unknown>} data включает branch_id */
export function storeOrderPolicy(data) {
    return request("POST", "/order/policy", data);
}

/** @param {Record<string, unknown>} data включает branch_id */
export function updateOrderPolicy(data) {
    return request("PUT", "/order/policy", data);
}

/** @param {Record<string, unknown>} data включает branch_id */
export function assignOrderPolicyRole(data) {
    return request("POST", "/order/policy/roles", data);
}

/** @param {number|string} branchId @param {number|string} productId @param {string} role */
export function deleteOrderPolicyRole(branchId, productId, role) {
    const params = new URLSearchParams({
        branch_id: String(branchId),
        product_id: String(productId),
        role: String(role),
    });
    return request("DELETE", `/order/policy/roles?${params}`);
}

/** @param {number|string} branchId */
export function syncOrderPolicyZones(branchId) {
    return request("POST", "/order/policy/sync-zones", { branch_id: Number(branchId) });
}
