import { listClients } from "../../../api/clients.js";
import { GROUP_LABELS } from "../types.js";

/**
 * @param {string} role
 * @returns {string|null}
 */
function clientRouteName(role) {
    if (role === "owner") {
        return "owner-client";
    }
    if (role === "administrator") {
        return "administrator-client";
    }
    return null;
}

/** @type {import('../types.js').SearchProvider} */
export const clientsProvider = {
    type: "client",
    label: GROUP_LABELS.client,
    async search(q, ctx) {
        const routeName = clientRouteName(ctx.role);
        if (!routeName) {
            return [];
        }
        const payload = await listClients({
            q,
            limit: ctx.limit,
            offset: 0,
        });
        const items = Array.isArray(payload?.items) ? payload.items : [];
        return items.slice(0, ctx.limit).map((client) => {
            const id = Number(client.id);
            const name = String(client.name || "").trim() || `Клиент #${id}`;
            const phone = String(client.phone || "").trim();
            return {
                id: `client:${id}`,
                type: "client",
                title: name,
                subtitle: phone || undefined,
                route: {
                    name: routeName,
                    params: { id: String(id) },
                },
            };
        });
    },
};
