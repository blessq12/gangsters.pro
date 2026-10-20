import { createRouter, createWebHistory } from "vue-router";
import { applyPageHead } from "../modules/shell/application/usePageHead";
import { trackYandexMetrikaSpaHit } from "../modules/shell/application/yandexMetrika";
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
    trackYandexMetrikaSpaHit(to.fullPath);
});

export default router;
