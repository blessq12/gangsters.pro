/**
 * Единый список маршрутов SPA: path, name, component и meta для публичной навигации.
 * Подписи ссылок шапки/футера — только в meta (см. resources/js/router/publicNav.ts).
 */

const brand = "Gangster's Sushi";

export const routeRecords = [
    {
        path: "/",
        name: "home",
        component: () => import("../pages/HomePage.vue"),
        meta: {
            navLabel: "Главная",
            navHeaderLeftOrder: 0,
            seo: {
                title: `Доставка суши и роллов в Томске | ${brand}`,
                description:
                    "Закажи суши, роллы и горячие блюда с доставкой по Томску. Актуальное меню, быстрая доставка и удобный заказ онлайн.",
                robots: "index,follow",
            },
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
            seo: {
                title: `О компании | ${brand}`,
                description:
                    "Gangster's Sushi — доставка с характером: тёмная эстетика, сочное меню и сервис, который не рассыпается на мелочах.",
                robots: "index,follow",
            },
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
            seo: {
                title: `Доставка еды в Томске | ${brand}`,
                description:
                    "Условия доставки Gangster's Sushi: зоны, сроки, оплата и минимальный заказ. Закажи суши и роллы с доставкой по Томску.",
                robots: "index,follow",
            },
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
            seo: {
                title: `Контакты | ${brand}`,
                description:
                    "Телефон, адрес и режим работы Gangster's Sushi в Томске. Свяжись с нами по заказу и вопросам доставки.",
                robots: "index,follow",
            },
        },
    },
    {
        path: "/reset-password",
        name: "client-reset-password",
        component: () => import("../pages/ClientResetPasswordPage.vue"),
        meta: {
            seo: {
                title: `Сброс пароля | ${brand}`,
                description: "Установи новый пароль для личного кабинета Gangster's Sushi.",
                robots: "noindex,nofollow",
            },
        },
    },
];
