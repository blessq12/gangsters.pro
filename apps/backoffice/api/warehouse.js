import { request } from "../http/transport.js";

/**
 * @param {{ offset?: number, limit?: number }} [query]
 */
function withPage(path, query = {}) {
    const params = new URLSearchParams();
    if (query.offset !== undefined) {
        params.set("offset", String(query.offset));
    }
    if (query.limit !== undefined) {
        params.set("limit", String(query.limit));
    }
    const qs = params.toString();
    return request("GET", `${path}${qs ? `?${qs}` : ""}`);
}

/** @param {{ offset?: number, limit?: number }} [query] */
export function listIngredients(query = {}) {
    return withPage("/warehouse/ingredients", query);
}

/** @param {number|string} id */
export function getIngredient(id) {
    return request("GET", `/warehouse/ingredients/${id}`);
}

/** @param {Record<string, unknown>} data */
export function storeIngredient(data) {
    return request("POST", "/warehouse/ingredients", data);
}

/** @param {number|string} id @param {Record<string, unknown>} data */
export function updateIngredient(id, data) {
    return request("PUT", `/warehouse/ingredients/${id}`, data);
}

/**
 * @param {number|string} branchId
 * @param {{ offset?: number, limit?: number }} [query]
 */
export function getBranchStock(branchId, query = {}) {
    return withPage(`/warehouse/branches/${branchId}/stock`, query);
}

/**
 * @param {number|string} branchId
 * @param {number|string} ingredientId
 */
export function getBranchIngredient(branchId, ingredientId) {
    return request("GET", `/warehouse/branches/${branchId}/ingredients/${ingredientId}`);
}

/**
 * @param {number|string} branchId
 * @param {Record<string, unknown>} data
 */
export function adjustBranchStock(branchId, data) {
    return request("POST", `/warehouse/branches/${branchId}/stock/adjust`, data);
}

/**
 * @param {number|string} branchId
 * @param {{ offset?: number, limit?: number }} [query]
 */
export function listBranchWriteOffs(branchId, query = {}) {
    return withPage(`/warehouse/branches/${branchId}/write-offs`, query);
}

/**
 * @param {number|string} branchId
 * @param {Record<string, unknown>} data
 */
export function storeBranchWriteOff(branchId, data) {
    return request("POST", `/warehouse/branches/${branchId}/write-offs`, data);
}
