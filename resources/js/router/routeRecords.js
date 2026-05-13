/**
 * Единый список маршрутов SPA: path, name, component и meta для публичной навигации.
 * Подписи ссылок шапки/футера — только в meta (см. resources/js/router/publicNav.ts).
 */

export const routeRecords = [
    {
        path: "/",
        name: "home",
        component: () => import("../pages/HomePage.vue"),
        meta: {
            navLabel: "Главная",
            navHeaderLeftOrder: 0,
        },
    },
    {
        path: "/about",
        name: "about",
        component: () => import("../pages/AboutPage.vue"),
        meta: {
            navLabel: "О компании",
            navHeaderLeftOrder: 1,
            navFooterOrder: 0,
        },
    },
    {
        path: "/delivery",
        name: "delivery",
        component: () => import("../pages/DeliveryPage.vue"),
        meta: {
            navLabel: "Доставка",
            navHeaderRightOrder: 0,
            navFooterOrder: 1,
        },
    },
    {
        path: "/contacts",
        name: "contacts",
        component: () => import("../pages/ContactsPage.vue"),
        meta: {
            navLabel: "Контакты",
            navHeaderRightOrder: 1,
            navFooterOrder: 2,
        },
    },
    {
        path: "/reset-password",
        name: "client-reset-password",
        component: () => import("../pages/ClientResetPasswordPage.vue"),
    },
];
