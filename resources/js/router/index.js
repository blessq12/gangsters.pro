import { createRouter, createWebHistory } from "vue-router";

const routes = [
    {
        path: "/",
        name: "home",
        component: () => import("../pages/HomePage.vue"),
    },
    {
        path: "/about",
        name: "about",
        component: () => import("../pages/AboutPage.vue"),
    },
    {
        path: "/delivery",
        name: "delivery",
        component: () => import("../pages/DeliveryPage.vue"),
    },
    {
        path: "/contacts",
        name: "contacts",
        component: () => import("../pages/ContactsPage.vue"),
    },
    {
        path: "/reset-password",
        name: "client-reset-password",
        component: () => import("../pages/ClientResetPasswordPage.vue"),
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0 };
    },
});

export default router;

