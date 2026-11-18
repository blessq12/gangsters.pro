import { request } from "../http/transport.js";

export function listIngredients() {
    return request("GET", "/warehouse/ingredients");
}

/** @param {Record<string, unknown>} data */
export function storeIngredient(data) {
    return request("POST", "/warehouse/ingredients", data);
}

/** @param {number|string} id @param {Record<string, unknown>} data */
export function updateIngredient(id, data) {
    return request("PUT", `/warehouse/ingredients/${id}`, data);
}

export function getStock() {
    return request("GET", "/warehouse/stock");
}

/** @param {Record<string, unknown>} data */
export function adjustStock(data) {
    return request("POST", "/warehouse/stock/adjust", data);
}

export function listWriteOffs() {
    return request("GET", "/warehouse/write-offs");
}

/** @param {Record<string, unknown>} data */
export function storeWriteOff(data) {
    return request("POST", "/warehouse/write-offs", data);
}
