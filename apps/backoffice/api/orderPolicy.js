import { request } from "../http/transport.js";

/** @param {number|string} branchId */
export function getOrderPolicy(branchId) {
    return request(
        "GET",
        `/order/commercial-rules?branch_id=${encodeURIComponent(String(branchId))}`,
    );
}

/** @param {Record<string, unknown>} data включает branch_id */
export function storeOrderPolicy(data) {
    return request("POST", "/order/commercial-rules", data);
}

/** @param {Record<string, unknown>} data включает branch_id */
export function updateOrderPolicy(data) {
    return request("PUT", "/order/commercial-rules", data);
}
