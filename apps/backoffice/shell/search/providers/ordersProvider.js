import { listOrders } from "../../../api/orders.js";
import { orderNumberOf } from "../../domain/orderStage.js";
import { GROUP_LABELS } from "../types.js";

/**
 * @param {string} role
 * @returns {string|null}
 */
function orderRouteName(role) {
    if (role === "owner") {
        return "owner-order";
    }
    if (role === "administrator") {
        return "administrator-order";
    }
    return null;
}

/** @type {import('../types.js').SearchProvider} */
export const ordersProvider = {
    type: "order",
    label: GROUP_LABELS.order,
    async search(q, ctx) {
        const routeName = orderRouteName(ctx.role);
        if (!routeName) {
            return [];
        }
        const payload = await listOrders({
            q,
            limit: ctx.limit,
            offset: 0,
            branch_id: ctx.branchId ?? undefined,
        });
        const items = Array.isArray(payload?.items) ? payload.items : [];
        return items.slice(0, ctx.limit).map((order) => {
            const id = Number(order.id);
            const number = orderNumberOf(order);
            const clientName = String(order?.client?.name || "").trim();
            const clientPhone = String(order?.client?.phone || "").trim();
            const subtitleParts = [clientName, clientPhone].filter(Boolean);
            return {
                id: `order:${id}`,
                type: "order",
                title: number || `Заказ #${id}`,
                subtitle: subtitleParts.join(" · ") || undefined,
                route: {
                    name: routeName,
                    params: { id: String(id) },
                },
            };
        });
    },
};
