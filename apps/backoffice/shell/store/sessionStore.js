import { defineStore } from "pinia";
import { loadRoleModule } from "../roles/loadRoleModule";

export const useSessionStore = defineStore("backoffice-session", {
    state: () => ({
        /** @type {import('../roles/contract.js').RoleModule|null} */
        roleModule: null,
        routesMountedFor: null,
    }),
    getters: {
        homeRouteName: (state) => state.roleModule?.home ?? null,
        navItems: (state) => {
            const items = state.roleModule?.nav ?? [];
            return [...items].sort((a, b) => (a.order ?? 0) - (b.order ?? 0));
        },
        allowedRouteNames: (state) => {
            const names = new Set(["backoffice-not-found"]);
            if (!state.roleModule) {
                return names;
            }
            names.add(state.roleModule.home);
            for (const route of state.roleModule.routes) {
                collectRouteNames(route, names);
            }
            return names;
        },
    },
    actions: {
        clear() {
            this.roleModule = null;
            this.routesMountedFor = null;
        },
        /**
         * @param {string} role
         * @param {import('vue-router').Router} router
         */
        async activateRole(role, router) {
            const module = await loadRoleModule(role);
            this.roleModule = module;
            if (this.routesMountedFor !== role) {
                for (const record of module.routes) {
                    if (!router.hasRoute(record.name) && record.name) {
                        router.addRoute(record);
                    } else if (!record.name) {
                        router.addRoute(record);
                    }
                }
                this.routesMountedFor = role;
            }
            return module;
        },
    },
});

/**
 * @param {import('vue-router').RouteRecordRaw} record
 * @param {Set<string>} names
 */
function collectRouteNames(record, names) {
    if (typeof record.name === "string") {
        names.add(record.name);
    }
    if (Array.isArray(record.children)) {
        for (const child of record.children) {
            collectRouteNames(child, names);
        }
    }
}
