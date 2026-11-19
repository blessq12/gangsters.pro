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

/** @param {Record<string, unknown>} data */
export function storeIngredient(data) {
    return request("POST", "/warehouse/ingredients", data);
}

/** @param {number|string} id @param {Record<string, unknown>} data */
export function updateIngredient(id, data) {
    return request("PUT", `/warehouse/ingredients/${id}`, data);
}

/** @param {{ offset?: number, limit?: number }} [query] */
export function getStock(query = {}) {
    return withPage("/warehouse/stock", query);
}

/** @param {Record<string, unknown>} data */
export function adjustStock(data) {
    return request("POST", "/warehouse/stock/adjust", data);
}

/** @param {{ offset?: number, limit?: number }} [query] */
export function listWriteOffs(query = {}) {
    return withPage("/warehouse/write-offs", query);
}

/** @param {Record<string, unknown>} data */
export function storeWriteOff(data) {
    return request("POST", "/warehouse/write-offs", data);
}
