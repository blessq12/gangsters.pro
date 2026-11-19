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

/** @param {{ offset?: number, limit?: number }} [query] */
export function listKitchens(query = {}) {
    return withPage("/company/kitchens", query);
}

/** @param {{ offset?: number, limit?: number }} [query] */
export function listDocuments(query = {}) {
    return withPage("/company/documents", query);
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
export function storeKitchen(data) {
    return request("POST", "/company/kitchens", data);
}

/** @param {number|string} id @param {Record<string, unknown>} data */
export function updateKitchen(id, data) {
    return request("PUT", `/company/kitchens/${id}`, data);
}

/** @param {Record<string, unknown>} data */
export function storeDocument(data) {
    return request("POST", "/company/documents", data);
}

/** @param {number|string} id @param {Record<string, unknown>} data */
export function updateDocument(id, data) {
    return request("PUT", `/company/documents/${id}`, data);
}
