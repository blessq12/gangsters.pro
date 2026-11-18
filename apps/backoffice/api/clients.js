import { request } from "../http/transport.js";

/**
 * @param {Record<string, string|number|boolean|undefined|null>} [query]
 */
export function listClients(query = {}) {
    const params = new URLSearchParams();
    Object.entries(query).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== "") {
            params.set(key, String(value));
        }
    });
    const qs = params.toString();
    return request("GET", `/clients${qs ? `?${qs}` : ""}`);
}

/** @param {number|string} id */
export function getClient(id) {
    return request("GET", `/clients/${id}`);
}
