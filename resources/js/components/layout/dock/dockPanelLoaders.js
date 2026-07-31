/** @type {Record<string, () => Promise<{ default: import('vue').Component }>>} */
export const DOCK_PANEL_LOADERS = {
    profile: () => import("./panels/ProfileDockPanel.vue"),
    cart: () => import("./panels/CartDockPanel.vue"),
    favorites: () => import("./panels/FavoritesDockPanel.vue"),
};
