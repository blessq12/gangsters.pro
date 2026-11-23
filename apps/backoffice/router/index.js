import { createRouter, createWebHistory } from "vue-router";
import { shellRoutes } from "./shellRoutes.js";
import { useAuthStore } from "../shell/store/authStore";
import { useSessionStore } from "../shell/store/sessionStore";
import { resolveGuardTarget } from "../shell/guard/resolveGuardTarget";
import { isForeignRolePath, isRegisteredRole } from "../shell/roles/registry";

const router = createRouter({
    history: createWebHistory("/backoffice"),
    routes: shellRoutes,
    scrollBehavior() {
        return { top: 0 };
    },
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();
    const session = useSessionStore();
    await auth.bootstrap();

    if (auth.authRequired && auth.isAuthenticated && auth.role && isRegisteredRole(auth.role)) {
        const routesJustMounted = session.routesMountedFor !== auth.role;
        if (routesJustMounted) {
            await session.activateRole(auth.role, router);
        }
        if (session.homeRouteName && isForeignRolePath(auth.role, to.path)) {
            return { name: session.homeRouteName };
        }
        if (routesJustMounted) {
            return to.fullPath;
        }
    }

    const decision = resolveGuardTarget({
        authRequired: auth.authRequired,
        isAuthenticated: auth.isAuthenticated,
        isGuestRoute: Boolean(to.meta.guest),
        role: auth.role,
        homeRouteName: session.homeRouteName,
        routeName: typeof to.name === "string" ? to.name : null,
        allowedRouteNames: new Set([
            ...session.allowedRouteNames,
            "backoffice-login",
            "backoffice-forgot-password",
            "backoffice-root",
            "backoffice-not-found",
        ]),
    });

    if (decision.type === "redirect") {
        if (decision.name === "backoffice-login") {
            return {
                name: "backoffice-login",
                query: { redirect: to.fullPath },
            };
        }
        return { name: decision.name };
    }

    return true;
});

export default router;
