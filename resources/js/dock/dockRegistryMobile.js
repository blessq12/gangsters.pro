import { defineAsyncComponent } from "vue";

export const dockItems = [
    {
        id: "profile",
        label: "Профиль",
        iconClass: "mdi-account-circle-outline",
        content: defineAsyncComponent(() =>
            import("../components/dock/ProfileDockPanelMobile.vue"),
        ),
    },
    {
        id: "cart",
        label: "Корзина",
        iconClass: "mdi-cart-outline",
        content: defineAsyncComponent(() =>
            import("../components/dock/CartDockPanelMobile.vue"),
        ),
    },
    {
        id: "favorites",
        label: "Избранное",
        iconClass: "mdi-heart-outline",
        content: defineAsyncComponent(() =>
            import("../components/dock/FavoritesDockPanelMobile.vue"),
        ),
    },
    {
        id: "delivery",
        label: "Доставка",
        iconClass: "mdi-truck-delivery-outline",
        content: defineAsyncComponent(() =>
            import("../components/dock/DeliveryDockPanelMobile.vue"),
        ),
    },
];

