import { createRouter, createWebHistory } from "vue-router";
import { routeRecords } from "./routeRecords.js";

const router = createRouter({
    history: createWebHistory(),
    routes: routeRecords,
    scrollBehavior() {
        return { top: 0 };
    },
});

export default router;
