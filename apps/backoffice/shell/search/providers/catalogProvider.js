import { listProducts } from "../../../api/catalog.js";
import { GROUP_LABELS } from "../types.js";

/**
 * @param {string} role
 * @param {number} productId
 * @returns {{ name: string, params?: Record<string, string>, query?: Record<string, string> }|null}
 */
function catalogRoute(role, productId) {
    if (role === "owner") {
        return {
            name: "owner-catalog-product",
            params: { id: String(productId) },
        };
    }
    if (role === "administrator") {
        return {
            name: "administrator-catalog",
            query: { q: String(productId) },
        };
    }
    if (role === "kitchen") {
        return {
            name: "production-queue",
        };
    }
    return null;
}

/** @type {import('../types.js').SearchProvider} */
export const catalogProvider = {
    type: "catalog",
    label: GROUP_LABELS.catalog,
    async search(q, ctx) {
        if (ctx.role === "kitchen") {
            // У кухни нет экрана каталога — пропускаем
            return [];
        }
        const payload = await listProducts({
            q,
            limit: ctx.limit,
            offset: 0,
        });
        const items = Array.isArray(payload?.items) ? payload.items : [];
        /** @type {import('../types.js').GlobalSearchHit[]} */
        const hits = [];
        for (const product of items.slice(0, ctx.limit)) {
            const id = Number(product.id);
            const route = catalogRoute(ctx.role, id);
            if (!route) {
                continue;
            }
            const name = String(product.name || "").trim() || `Товар #${id}`;
            const sku = String(product.sku || "").trim();
            hits.push({
                id: `catalog:${id}`,
                type: "catalog",
                title: name,
                subtitle: sku || undefined,
                route,
            });
        }
        return hits;
    },
};
