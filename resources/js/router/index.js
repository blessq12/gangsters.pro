import { createRouter, createWebHistory } from "vue-router";
import { applyPageHead } from "../features/seo/usePageHead";
import { routeRecords } from "./routeRecords.js";

const router = createRouter({
    history: createWebHistory(),
    routes: routeRecords,
    scrollBehavior() {
        return { top: 0 };
    },
});

router.afterEach((to) => {
    applyPageHead(to);
});

export default router;
