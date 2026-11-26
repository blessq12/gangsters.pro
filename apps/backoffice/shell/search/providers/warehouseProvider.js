import { listIngredients } from "../../../api/warehouse.js";
import { GROUP_LABELS } from "../types.js";

/**
 * @param {string} role
 * @param {number} ingredientId
 * @param {number|null} branchId
 * @returns {{ name: string, params?: Record<string, string|number> }|null}
 */
function warehouseRoute(role, ingredientId, branchId) {
    if (role === "owner") {
        return {
            name: "owner-warehouse-ingredient",
            params: { id: String(ingredientId) },
        };
    }
    if (role === "kitchen" && branchId) {
        return {
            name: "production-warehouse-item",
            params: {
                branchId: String(branchId),
                id: String(ingredientId),
            },
        };
    }
    return null;
}

/**
 * @param {Record<string, unknown>} row
 * @param {string} q
 * @returns {boolean}
 */
function ingredientMatches(row, q) {
    const id = String(row.id ?? "");
    const name = String(row.name ?? "").toLowerCase();
    const unit = String(row.unit ?? "").toLowerCase();
    const sku = String(row.sku ?? "").toLowerCase();
    return id.includes(q) || name.includes(q) || unit.includes(q) || sku.includes(q);
}

/** @type {import('../types.js').SearchProvider} */
export const warehouseProvider = {
    type: "warehouse",
    label: GROUP_LABELS.warehouse,
    async search(q, ctx) {
        if (ctx.role !== "owner" && ctx.role !== "kitchen") {
            return [];
        }
        const needle = q.trim().toLowerCase();
        const payload = await listIngredients({
            limit: 50,
            offset: 0,
        });
        const items = Array.isArray(payload?.items) ? payload.items : [];
        /** @type {import('../types.js').GlobalSearchHit[]} */
        const hits = [];
        for (const row of items
            .filter((item) => ingredientMatches(item, needle))
            .slice(0, ctx.limit)) {
            const id = Number(row.id);
            const route = warehouseRoute(ctx.role, id, ctx.branchId);
            if (!route) {
                continue;
            }
            const name = String(row.name || "").trim() || `Ингредиент #${id}`;
            const unit = String(row.unit || "").trim();
            hits.push({
                id: `warehouse:${id}`,
                type: "warehouse",
                title: name,
                subtitle: unit || undefined,
                route,
            });
        }
        return hits;
    },
};
