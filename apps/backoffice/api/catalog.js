import { request } from "../http/transport.js";

export function getCatalog() {
    return request("GET", "/catalog");
}

/** @param {Record<string, unknown>} data */
export function storeProduct(data) {
    return request("POST", "/catalog/products", data);
}

/** @param {number|string} id @param {Record<string, unknown>} data */
export function updateProduct(id, data) {
    return request("PUT", `/catalog/products/${id}`, data);
}

/** @param {Record<string, unknown>} data */
export function storeCategory(data) {
    return request("POST", "/catalog/categories", data);
}

/** @param {number|string} id @param {Record<string, unknown>} data */
export function updateCategory(id, data) {
    return request("PUT", `/catalog/categories/${id}`, data);
}

/** @param {Record<string, unknown>} data */
export function storeSet(data) {
    return request("POST", "/catalog/sets", data);
}

/** @param {number|string} id @param {Record<string, unknown>} data */
export function updateSet(id, data) {
    return request("PUT", `/catalog/sets/${id}`, data);
}

/** @param {Record<string, unknown>} data */
export function storeTag(data) {
    return request("POST", "/catalog/tags", data);
}

/** @param {number|string} id @param {Record<string, unknown>} data */
export function updateTag(id, data) {
    return request("PUT", `/catalog/tags/${id}`, data);
}
