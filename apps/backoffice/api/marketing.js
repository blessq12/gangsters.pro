import { request } from "../http/transport.js";

export function getMarketing() {
    return request("GET", "/marketing");
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
export function listBanners(query = {}) {
    return withPage("/marketing/banners", query);
}

/** @param {{ offset?: number, limit?: number }} [query] */
export function listPromotions(query = {}) {
    return withPage("/marketing/promotions", query);
}

/** @param {Record<string, unknown>} data */
export function storeBanner(data) {
    return request("POST", "/marketing/banners", data);
}

/** @param {number|string} id @param {Record<string, unknown>} data */
export function updateBanner(id, data) {
    return request("PUT", `/marketing/banners/${id}`, data);
}

/** @param {Record<string, unknown>} data */
export function storePromotion(data) {
    return request("POST", "/marketing/promotions", data);
}

/** @param {number|string} id @param {Record<string, unknown>} data */
export function updatePromotion(id, data) {
    return request("PUT", `/marketing/promotions/${id}`, data);
}

/** @param {Record<string, unknown>} data */
export function saveDeliveryConditions(data) {
    return request("PUT", "/marketing/delivery-conditions", data);
}
