import { request } from "../http/transport.js";

export function getQueue() {
    return request("GET", "/production/queue");
}

/** @param {number|string} orderId */
export function getTicket(orderId) {
    return request("GET", `/production/tickets/${orderId}`);
}

/** @param {number|string} orderId */
export function enqueueTicket(orderId) {
    return request("POST", `/production/tickets/${orderId}/queue`);
}

/** @param {number|string} orderId */
export function startTicket(orderId) {
    return request("POST", `/production/tickets/${orderId}/start`);
}

/** @param {number|string} orderId */
export function completeTicket(orderId) {
    return request("POST", `/production/tickets/${orderId}/complete`);
}

/** @param {number|string} orderId */
export function cancelTicket(orderId) {
    return request("POST", `/production/tickets/${orderId}/cancel`);
}
