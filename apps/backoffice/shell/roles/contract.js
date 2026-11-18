/**
 * Role module contract (shell ↔ role).
 *
 * @typedef {Object} RoleNavItem
 * @property {string} label
 * @property {string} routeName
 * @property {string} [icon]
 * @property {number} order
 *
 * @typedef {Object} RoleModule
 * @property {string} role API role key (e.g. kitchen, courier)
 * @property {string} home Route name after login / foreign-route redirect
 * @property {RoleNavItem[]} nav
 * @property {import('vue-router').RouteRecordRaw[]} routes
 * @property {import('vue').Component} [layout] Optional wrapper under shell topbar
 */

/**
 * @param {unknown} module
 * @returns {asserts module is RoleModule}
 */
export function assertRoleModule(module) {
    if (!module || typeof module !== "object") {
        throw new Error("Role module must be an object.");
    }
    if (typeof module.role !== "string" || module.role === "") {
        throw new Error("Role module.role is required.");
    }
    if (typeof module.home !== "string" || module.home === "") {
        throw new Error("Role module.home is required.");
    }
    if (!Array.isArray(module.nav)) {
        throw new Error("Role module.nav must be an array.");
    }
    if (!Array.isArray(module.routes)) {
        throw new Error("Role module.routes must be an array.");
    }
}
