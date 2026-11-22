import { request } from "../http/transport.js";

/**
 * @param {Record<string, string|number|boolean|undefined|null>} [query]
 */
export function listEmployees(query = {}) {
    const params = new URLSearchParams();
    Object.entries(query).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== "") {
            params.set(key, String(value));
        }
    });
    const qs = params.toString();
    return request("GET", `/employees${qs ? `?${qs}` : ""}`);
}

/** @param {number|string} id */
export function getEmployee(id) {
    return request("GET", `/employees/${id}`);
}

/** @param {Record<string, unknown>} data */
export function storeEmployee(data) {
    return request("POST", "/employees", data);
}

/** @param {number|string} id @param {Record<string, unknown>} data */
export function updateEmployee(id, data) {
    return request("PUT", `/employees/${id}`, data);
}

/** @param {number|string} id */
export function toggleEmployeeActive(id) {
    return request("POST", `/employees/${id}/toggle-active`);
}
