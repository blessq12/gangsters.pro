import { request } from "../http/transport.js";

export function getPromotionPolicy() {
    return request("GET", "/promotion-policy");
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
export function listPromotionRoles(query = {}) {
    return withPage("/promotion-policy/roles", query);
}

/** @param {{ offset?: number, limit?: number }} [query] */
export function listPromotionZones(query = {}) {
    return withPage("/promotion-policy/zones", query);
}

/** @param {Record<string, unknown>} data */
export function storePromotionPolicy(data) {
    return request("POST", "/promotion-policy", data);
}

/** @param {Record<string, unknown>} data */
export function updatePromotionPolicy(data) {
    return request("PUT", "/promotion-policy", data);
}

/** @param {Record<string, unknown>} data */
export function assignProductRole(data) {
    return request("POST", "/promotion-policy/roles", data);
}

export function syncZones() {
    return request("POST", "/promotion-policy/sync-zones");
}
