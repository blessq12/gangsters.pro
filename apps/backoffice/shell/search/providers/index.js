import { catalogProvider } from "./catalogProvider.js";
import { clientsProvider } from "./clientsProvider.js";
import { deliveryProvider } from "./deliveryProvider.js";
import { ordersProvider } from "./ordersProvider.js";
import { warehouseProvider } from "./warehouseProvider.js";
import { normalizeSearchRole } from "../types.js";

/** @type {Record<string, import('../types.js').SearchProvider[]>} */
const REGISTRY = {
    owner: [ordersProvider, clientsProvider, deliveryProvider, warehouseProvider, catalogProvider],
    administrator: [ordersProvider, clientsProvider, deliveryProvider, catalogProvider],
    kitchen: [warehouseProvider],
};

/**
 * @param {string} role
 * @returns {import('../types.js').SearchProvider[]}
 */
export function providersForRole(role) {
    const key = normalizeSearchRole(role);
    return REGISTRY[key] || [];
}

/**
 * @param {string} role
 * @returns {boolean}
 */
export function roleHasGlobalSearch(role) {
    return providersForRole(role).length > 0;
}
