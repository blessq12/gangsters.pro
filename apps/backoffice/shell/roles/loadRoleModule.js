import { assertRoleModule } from "./contract";
import { isRegisteredRole, roleRegistry } from "./registry";

/** @type {Map<string, import('./contract.js').RoleModule>} */
const cache = new Map();

/**
 * @param {string} role
 * @returns {Promise<import('./contract.js').RoleModule>}
 */
export async function loadRoleModule(role) {
    if (!isRegisteredRole(role)) {
        throw new Error(`Unknown backoffice role: ${role}`);
    }
    if (cache.has(role)) {
        return cache.get(role);
    }
    const loaded = await roleRegistry[role]();
    const module = loaded.default ?? loaded;
    assertRoleModule(module);
    cache.set(role, module);
    return module;
}
