/**
 * Единый список вкладок дока (метаданные без привязки к платформе).
 * @type {ReadonlyArray<{ id: string, label: string, iconClass: string }>}
 */
export const DOCK_META = Object.freeze([
    {
        id: "profile",
        label: "Профиль",
        iconClass: "mdi-account-circle-outline",
    },
    {
        id: "cart",
        label: "Корзина",
        iconClass: "mdi-cart-outline",
    },
    {
        id: "favorites",
        label: "Избранное",
        iconClass: "mdi-heart-outline",
    },
    {
        id: "delivery",
        label: "Доставка",
        iconClass: "mdi-truck-delivery-outline",
    },
]);
