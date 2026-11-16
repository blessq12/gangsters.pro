import { createRouter, createWebHistory } from "vue-router";
import { routeRecords } from "./routeRecords.js";
import { useAuthStore } from "../shell/store/authStore";
import { homeRoute } from "../shell/authConfig";

const router = createRouter({
    history: createWebHistory("/backoffice"),
    routes: routeRecords,
    scrollBehavior() {
        return { top: 0 };
    },
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();
    await auth.bootstrap();

    if (!auth.authRequired) {
        return true;
    }

    if (to.meta.guest) {
        if (auth.isAuthenticated) {
            return homeRoute();
        }
        return true;
    }

    if (!auth.isAuthenticated) {
        return {
            name: "backoffice-login",
            query: { redirect: to.fullPath },
        };
    }

    return true;
});

export default router;
