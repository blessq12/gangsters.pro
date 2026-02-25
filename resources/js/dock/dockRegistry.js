import ProfileDockPanel from "../components/dock/ProfileDockPanel.vue";
import CartDockPanel from "../components/dock/CartDockPanel.vue";
import FavoritesDockPanel from "../components/dock/FavoritesDockPanel.vue";
import DeliveryDockPanel from "../components/dock/DeliveryDockPanel.vue";
import NotificationsDockPanel from "../components/dock/NotificationsDockPanel.vue";

export const dockItems = [
    {
        id: "profile",
        label: "Профиль",
        iconClass: "mdi-account-circle-outline",
        content: ProfileDockPanel,
    },
    {
        id: "cart",
        label: "Корзина",
        iconClass: "mdi-cart-outline",
        content: CartDockPanel,
    },
    {
        id: "favorites",
        label: "Избранное",
        iconClass: "mdi-heart-outline",
        content: FavoritesDockPanel,
    },
    {
        id: "delivery",
        label: "Доставка",
        iconClass: "mdi-truck-delivery-outline",
        content: DeliveryDockPanel,
    },
    {
        id: "notifications",
        label: "Уведомления",
        iconClass: "mdi-bell-outline",
        content: NotificationsDockPanel,
    },
];

