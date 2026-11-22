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

/**
 * @param {number|string} id
 * @param {Record<string, unknown>} data
 */
export function updateClientProfile(id, data) {
    return request("PATCH", `/clients/${id}`, data);
}

/**
 * @param {number|string} clientId
 * @param {Record<string, unknown>} data
 */
export function addClientAddress(clientId, data) {
    return request("POST", `/clients/${clientId}/addresses`, data);
}

/**
 * @param {number|string} clientId
 * @param {string} addressId
 * @param {Record<string, unknown>} data
 */
export function updateClientAddress(clientId, addressId, data) {
    return request("PUT", `/clients/${clientId}/addresses/${addressId}`, data);
}

/**
 * @param {number|string} clientId
 * @param {string} addressId
 */
export function deleteClientAddress(clientId, addressId) {
    return request("DELETE", `/clients/${clientId}/addresses/${addressId}`);
}

/**
 * @param {number|string} clientId
 * @param {string} addressId
 */
export function setDefaultClientAddress(clientId, addressId) {
    return request("POST", `/clients/${clientId}/addresses/${addressId}/default`);
}
