import { listRouteSheets } from "../../../api/delivery.js";
import { GROUP_LABELS } from "../types.js";

/**
 * @param {string} role
 * @returns {string|null}
 */
function deliveryRouteName(role) {
    if (role === "owner") {
        return "owner-delivery";
    }
    if (role === "administrator") {
        return "administrator-logistics";
    }
    return null;
}

/**
 * @param {Record<string, unknown>} sheet
 * @param {string} q
 * @returns {boolean}
 */
function sheetMatches(sheet, q) {
    const id = String(sheet.id ?? "");
    const status = String(sheet.status ?? "").toLowerCase();
    const courier = String(sheet.courier_name ?? sheet.courier?.name ?? "").toLowerCase();
    return id.includes(q) || status.includes(q) || courier.includes(q);
}

/** @type {import('../types.js').SearchProvider} */
export const deliveryProvider = {
    type: "delivery",
    label: GROUP_LABELS.delivery,
    async search(q, ctx) {
        const routeName = deliveryRouteName(ctx.role);
        if (!routeName || !ctx.branchId) {
            return [];
        }
        const needle = q.trim().toLowerCase();
        const payload = await listRouteSheets({
            branch_id: ctx.branchId,
            limit: 50,
            offset: 0,
        });
        const items = Array.isArray(payload?.items) ? payload.items : [];
        return items
            .filter((sheet) => sheetMatches(sheet, needle))
            .slice(0, ctx.limit)
            .map((sheet) => {
                const id = Number(sheet.id);
                const status = String(sheet.status || "").trim();
                return {
                    id: `delivery:${id}`,
                    type: "delivery",
                    title: `Маршрутный лист #${id}`,
                    subtitle: status || undefined,
                    route: {
                        name: routeName,
                        query: { sheet: String(id) },
                    },
                };
            });
    },
};
