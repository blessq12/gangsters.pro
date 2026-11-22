import { request } from "../http/transport.js";

/** @param {number|string} branchId */
export function getPromotionPolicy(branchId) {
    return request("GET", `/promotion-policy?branch_id=${encodeURIComponent(String(branchId))}`);
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
export function listPromotionRoles(branchId, query = {}) {
    return withPage("/promotion-policy/roles", { ...query, branch_id: branchId });
}

/** @param {number|string} branchId @param {{ offset?: number, limit?: number }} [query] */
export function listPromotionZones(branchId, query = {}) {
    return withPage("/promotion-policy/zones", { ...query, branch_id: branchId });
}

/** @param {Record<string, unknown>} data включает branch_id */
export function storePromotionPolicy(data) {
    return request("POST", "/promotion-policy", data);
}

/** @param {Record<string, unknown>} data включает branch_id */
export function updatePromotionPolicy(data) {
    return request("PUT", "/promotion-policy", data);
}

/** @param {Record<string, unknown>} data включает branch_id */
export function assignProductRole(data) {
    return request("POST", "/promotion-policy/roles", data);
}

/** @param {number|string} branchId @param {number|string} productId @param {string} role */
export function deleteProductRole(branchId, productId, role) {
    const params = new URLSearchParams({
        branch_id: String(branchId),
        product_id: String(productId),
        role: String(role),
    });
    return request("DELETE", `/promotion-policy/roles?${params}`);
}

/** @param {number|string} branchId */
export function syncZones(branchId) {
    return request("POST", "/promotion-policy/sync-zones", { branch_id: Number(branchId) });
}
