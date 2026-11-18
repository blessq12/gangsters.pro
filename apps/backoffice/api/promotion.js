import { request } from "../http/transport.js";

export function getPromotionPolicy() {
    return request("GET", "/promotion-policy");
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
