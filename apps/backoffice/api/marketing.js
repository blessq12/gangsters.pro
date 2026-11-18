import { request } from "../http/transport.js";

export function getMarketing() {
    return request("GET", "/marketing");
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
