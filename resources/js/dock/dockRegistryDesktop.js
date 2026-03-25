import { defineAsyncComponent } from "vue";

export const dockItems = [
    {
        id: "profile",
        label: "Профиль",
        iconClass: "mdi-account-circle-outline",
        content: defineAsyncComponent(() =>
            import("../components/dock/ProfileDockPanelDesktop.vue"),
        ),
    },
    {
        id: "cart",
        label: "Корзина",
        iconClass: "mdi-cart-outline",
        content: defineAsyncComponent(() =>
            import("../components/dock/CartDockPanelDesktop.vue"),
        ),
    },
    {
        id: "favorites",
        label: "Избранное",
        iconClass: "mdi-heart-outline",
        content: defineAsyncComponent(() =>
            import("../components/dock/FavoritesDockPanelDesktop.vue"),
        ),
    },
    {
        id: "delivery",
        label: "Доставка",
        iconClass: "mdi-truck-delivery-outline",
        content: defineAsyncComponent(() =>
            import("../components/dock/DeliveryDockPanelDesktop.vue"),
        ),
    },
];

