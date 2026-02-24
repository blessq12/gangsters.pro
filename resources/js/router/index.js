import { createRouter, createWebHistory } from "vue-router";
import HomePage from "../pages/HomePage.vue";
import AboutPage from "../pages/AboutPage.vue";
import DeliveryPage from "../pages/DeliveryPage.vue";
import ContactsPage from "../pages/ContactsPage.vue";

const routes = [
    {
        path: "/",
        name: "home",
        component: HomePage,
    },
    {
        path: "/about",
        name: "about",
        component: AboutPage,
    },
    {
        path: "/delivery",
        name: "delivery",
        component: DeliveryPage,
    },
    {
        path: "/contacts",
        name: "contacts",
        component: ContactsPage,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;

