/**
 * @typedef {'order'|'client'|'delivery'|'warehouse'|'catalog'} GlobalSearchType
 */

/**
 * @typedef {{
 *   id: string,
 *   type: GlobalSearchType,
 *   title: string,
 *   subtitle?: string,
 *   route: {
 *     name: string,
 *     params?: Record<string, string|number>,
 *     query?: Record<string, string>,
 *   },
 * }} GlobalSearchHit
 */

/**
 * @typedef {{
 *   role: string,
 *   branchId: number|null,
 *   limit: number,
 * }} GlobalSearchContext
 */

/**
 * @typedef {{
 *   type: GlobalSearchType,
 *   label: string,
 *   search: (q: string, ctx: GlobalSearchContext) => Promise<GlobalSearchHit[]>,
 * }} SearchProvider
 */

/**
 * @typedef {{
 *   type: GlobalSearchType,
 *   label: string,
 *   hits: GlobalSearchHit[],
 * }} GlobalSearchGroup
 */

/** @type {Record<GlobalSearchType, string>} */
export const GROUP_LABELS = {
    order: "Заказы",
    client: "Клиенты",
    delivery: "Доставка",
    warehouse: "Склад",
    catalog: "Каталог",
};

/**
 * @param {string} role
 * @returns {string}
 */
export function normalizeSearchRole(role) {
    const value = String(role || "").trim();
    if (value === "production") {
        return "kitchen";
    }
    return value;
}
