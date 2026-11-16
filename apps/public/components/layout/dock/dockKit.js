import { defineAsyncComponent } from "vue";

/** @type {Record<string, () => Promise<{ default: import('vue').Component }>>} */
export const DOCK_PANEL_LOADERS = {
    profile: () => import("./panels/ProfileDockPanel.vue"),
    cart: () => import("./panels/CartDockPanel.vue"),
    favorites: () => import("./panels/FavoritesDockPanel.vue"),
};

/**
 * Единый список вкладок дока (метаданные без привязки к платформе).
 * @type {ReadonlyArray<{ id: string, label: string, iconClass: string }>} 
 */
export const DOCK_META = Object.freeze([
    { id: "cart", label: "Корзина", iconClass: "mdi-cart-outline" },
    { id: "profile", label: "Профиль", iconClass: "mdi-account-circle-outline" },
    { id: "favorites", label: "Избранное", iconClass: "mdi-heart-outline" },
]);

/** Единый список вкладок дока для мобильной и десктопной разметки. */
export function createDockItems() {
    return DOCK_META.map((meta) => {
        const load = DOCK_PANEL_LOADERS[meta.id];
        if (!load) {
            throw new Error(`createDockItems: no loader for id "${meta.id}"`);
        }
        return { ...meta, content: defineAsyncComponent(load) };
    });
}

export const dockItems = createDockItems();
