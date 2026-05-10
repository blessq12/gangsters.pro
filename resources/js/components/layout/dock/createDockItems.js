import { defineAsyncComponent } from "vue";
import { DOCK_META } from "./dockMeta";

const PANEL_LOADERS = {
    profile: () => import("./panels/ProfileDockPanel.vue"),
    cart: () => import("./panels/CartDockPanel.vue"),
    favorites: () => import("./panels/FavoritesDockPanel.vue"),
    delivery: () => import("./panels/DeliveryDockPanel.vue"),
};

/**
 * Единый список вкладок дока: одни и те же панели на мобиле и десктопе (responsive-разметка).
 */
export function createDockItems() {
    return DOCK_META.map((meta) => {
        const load = PANEL_LOADERS[meta.id];
        if (!load) {
            throw new Error(`createDockItems: no loader for id "${meta.id}"`);
        }
        return {
            ...meta,
            content: defineAsyncComponent(load),
        };
    });
}
