/**
 * Static role → lazy module loader.
 * Shell may import this file only — never roles/* directly.
 * API StaffRole.Kitchen maps to the production UI module.
 *
 * @type {Record<string, () => Promise<{ default: import('./contract.js').RoleModule }>>}
 */
export const roleRegistry = {
    owner: () => import("../../roles/owner/index.js"),
    administrator: () => import("../../roles/administrator/index.js"),
    kitchen: () => import("../../roles/production/index.js"),
    courier: () => import("../../roles/courier/index.js"),
};

/** URL prefix owned by each API role (for foreign-path redirects). */
export const rolePathPrefix = {
    owner: "/owner",
    administrator: "/administrator",
    kitchen: "/production",
    courier: "/courier",
};

/**
 * @param {string|null|undefined} role
 * @returns {boolean}
 */
export function isRegisteredRole(role) {
    return typeof role === "string" && Object.prototype.hasOwnProperty.call(roleRegistry, role);
}

/**
 * @param {string|null|undefined} role
 * @param {string} path
 * @returns {boolean}
 */
export function isForeignRolePath(role, path) {
    if (!isRegisteredRole(role)) {
        return false;
    }
    for (const [otherRole, prefix] of Object.entries(rolePathPrefix)) {
        if (otherRole !== role && path.startsWith(prefix)) {
            return true;
        }
    }
    return false;
}
