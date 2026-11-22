import { request } from "../http/transport.js";

export function getCompany() {
    return request("GET", "/company");
}

/**
 * @param {string} path
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
export function listBranches(query = {}) {
    return withPage("/company/branches", query);
}

/** @param {number|string} id */
export function getBranch(id) {
    return request("GET", `/company/branches/${id}`);
}

/** @param {{ offset?: number, limit?: number }} [query] */
export function listDocuments(query = {}) {
    return withPage("/company/documents", query);
}

/** @param {number|string} id */
export function getDocument(id) {
    return request("GET", `/company/documents/${id}`);
}

/** @param {Record<string, unknown>} data */
export function storeCompany(data) {
    return request("POST", "/company", data);
}

/** @param {Record<string, unknown>} data */
export function updateCompany(data) {
    return request("PUT", "/company", data);
}

/** @param {Record<string, unknown>} data */
export function storeBranch(data) {
    return request("POST", "/company/branches", data);
}

/** @param {number|string} id @param {Record<string, unknown>} data */
export function updateBranch(id, data) {
    return request("PUT", `/company/branches/${id}`, data);
}

/** @param {Record<string, unknown>} data */
export function storeDocument(data) {
    return request("POST", "/company/documents", data);
}

/** @param {number|string} id @param {Record<string, unknown>} data */
export function updateDocument(id, data) {
    return request("PUT", `/company/documents/${id}`, data);
}

/** @param {number|string} branchId */
export function getBranchMenu(branchId) {
    return request("GET", `/company/branches/${branchId}/menu`);
}

/**
 * @param {number|string} branchId
 * @param {number|string} catalogItemId
 * @param {{ is_available: boolean, price_kopecks: number }} data
 */
export function saveBranchMenuItem(branchId, catalogItemId, data) {
    return request("PUT", `/company/branches/${branchId}/menu/${catalogItemId}`, data);
}

/** @param {number|string} branchId @param {number|string} catalogItemId */
export function deleteBranchMenuItem(branchId, catalogItemId) {
    return request("DELETE", `/company/branches/${branchId}/menu/${catalogItemId}`);
}

/**
 * Клонирование меню / политик / доставки между филиалами.
 * @param {{
 *   source_branch_id: number,
 *   target_branch_ids: number[],
 *   copy_menu?: boolean,
 *   copy_promotions?: boolean,
 *   copy_delivery?: boolean,
 *   mode?: 'overwrite'|'skip_existing',
 * }} data
 */
export function cloneBranchSettings(data) {
    return request("POST", "/company/branches/clone-settings", data);
}
