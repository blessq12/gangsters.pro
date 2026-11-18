import { request } from "../http/transport.js";

export function getDelivery() {
    return request("GET", "/delivery");
}

/** @param {Record<string, unknown>} data */
export function storeDelivery(data) {
    return request("POST", "/delivery", data);
}

/** @param {Record<string, unknown>} data */
export function updateDelivery(data) {
    return request("PUT", "/delivery", data);
}

/** @param {Record<string, unknown>} data */
export function storeZone(data) {
    return request("POST", "/delivery/zones", data);
}

/** @param {number|string} id @param {Record<string, unknown>} data */
export function updateZone(id, data) {
    return request("PUT", `/delivery/zones/${id}`, data);
}

export function listRouteSheets() {
    return request("GET", "/delivery/route-sheets");
}

export function listCourierRouteSheets() {
    return request("GET", "/courier/route-sheets");
}

/** @param {number|string} id */
export function getRouteSheet(id) {
    return request("GET", `/delivery/route-sheets/${id}`);
}

/** @param {{ order_ids: number[] }} data */
export function createRouteSheet(data) {
    return request("POST", "/delivery/route-sheets", data);
}

/** @param {number|string} id @param {{ courier_id: number }} data */
export function takeRouteSheet(id, data) {
    return request("POST", `/delivery/route-sheets/${id}/take`, data);
}

/**
 * @param {number|string} sheetId
 * @param {number|string} orderId
 * @param {{ outcome: string, reason?: string|null }} data
 */
export function settleStop(sheetId, orderId, data) {
    return request(
        "POST",
        `/delivery/route-sheets/${sheetId}/stops/${orderId}/settle`,
        data,
    );
}
