import { request } from "../http/transport.js";

/**
 * @param {string} path
 * @param {{ offset?: number, limit?: number, branch_id?: number|string, status?: string }} [query]
 */
function withPage(path, query = {}) {
    const params = new URLSearchParams();
    if (query.branch_id !== undefined) {
        params.set("branch_id", String(query.branch_id));
    }
    if (query.offset !== undefined) {
        params.set("offset", String(query.offset));
    }
    if (query.limit !== undefined) {
        params.set("limit", String(query.limit));
    }
    if (query.status !== undefined && query.status !== null && query.status !== "") {
        params.set("status", String(query.status));
    }
    const qs = params.toString();
    return request("GET", `${path}${qs ? `?${qs}` : ""}`);
}

/** @param {number|string} branchId */
export function getDelivery(branchId) {
    return request("GET", `/delivery?branch_id=${encodeURIComponent(String(branchId))}`);
}

/** @param {Record<string, unknown>} data включает branch_id */
export function storeDelivery(data) {
    return request("POST", "/delivery", data);
}

/** @param {Record<string, unknown>} data включает branch_id */
export function updateDelivery(data) {
    return request("PUT", "/delivery", data);
}

/** @param {Record<string, unknown>} data включает branch_id */
export function storeZone(data) {
    return request("POST", "/delivery/zones", data);
}

/** @param {number|string} id @param {Record<string, unknown>} data включает branch_id */
export function updateZone(id, data) {
    return request("PUT", `/delivery/zones/${id}`, data);
}

/**
 * Полная замена зон филиала (с геометрией).
 * @param {{ branch_id: number|string, zones: Array<Record<string, unknown>> }} data
 */
export function replaceZones(data) {
    return request("PUT", "/delivery/zones", data);
}

/** @param {number|string} branchId @param {{ offset?: number, limit?: number }} [query] */
export function listZones(branchId, query = {}) {
    return withPage("/delivery/zones", { ...query, branch_id: branchId });
}

/** @param {{ offset?: number, limit?: number }} [query] */
export function listRouteSheets(query = {}) {
    return withPage("/delivery/route-sheets", query);
}

/** @param {{ offset?: number, limit?: number, status?: string }} [query] */
export function listCourierRouteSheets(query = {}) {
    return withPage("/courier/route-sheets", query);
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
 * @param {{ outcome: string, reason?: string|null, payment_method?: string|null }} data
 */
export function settleStop(sheetId, orderId, data) {
    return request(
        "POST",
        `/delivery/route-sheets/${sheetId}/stops/${orderId}/settle`,
        data,
    );
}
