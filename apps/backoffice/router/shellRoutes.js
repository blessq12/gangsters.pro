export const shellRoutes = [
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
        name: "backoffice-root",
        component: () => import("../shell/pages/RootRedirectPage.vue"),
    },
    {
        path: "/:pathMatch(.*)*",
        name: "backoffice-not-found",
        component: () => import("../shell/pages/NotFoundPage.vue"),
    },
];
