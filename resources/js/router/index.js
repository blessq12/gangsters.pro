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
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;

