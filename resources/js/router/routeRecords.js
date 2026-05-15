/**
 * Единый список маршрутов SPA: path, name, component и meta для публичной навигации.
 * Подписи ссылок шапки/футера — только в meta (см. resources/js/router/publicNav.ts).
 * SEO-тексты страниц — resources/site/seo-pages.json.
 */

import seoPages from "../../site/seo-pages.json";

function seoForPath(path) {
    return seoPages[path] ?? seoPages["/"];
}

export const routeRecords = [
    {
        path: "/",
        name: "home",
        component: () => import("../pages/HomePage.vue"),
        meta: {
            navLabel: "Главная",
            navHeaderLeftOrder: 0,
            seo: seoForPath("/"),
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
            seo: seoForPath("/about"),
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
            seo: seoForPath("/delivery"),
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
            seo: seoForPath("/contacts"),
        },
    },
    {
        path: "/reset-password",
        name: "client-reset-password",
        component: () => import("../pages/ClientResetPasswordPage.vue"),
        meta: {
            seo: seoForPath("/reset-password"),
        },
    },
];
