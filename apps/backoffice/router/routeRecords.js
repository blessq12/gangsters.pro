/**
 * Backoffice SPA skeleton routes. Base: /backoffice
 */

export const routeRecords = [
    {
        path: "/login",
        name: "backoffice-login",
        component: () => import("../shell/pages/LoginPage.vue"),
        meta: { guest: true },
    },
    {
        path: "/forgot-password",
        name: "backoffice-forgot-password",
        component: () => import("../shell/pages/ForgotPasswordPage.vue"),
        meta: { guest: true },
    },
    {
        path: "/",
        name: "backoffice-home",
        component: () => import("../shell/pages/HomePage.vue"),
    },
    {
        path: "/:pathMatch(.*)*",
        name: "backoffice-not-found",
        component: () => import("../shell/pages/NotFoundPage.vue"),
    },
];
