import assert from "node:assert/strict";
import { describe, it } from "node:test";
import { resolveGuardTarget } from "./resolveGuardTarget.js";

const roles = [
    { role: "owner", home: "owner-dashboard" },
    { role: "administrator", home: "administrator-live-orders" },
    { role: "kitchen", home: "production-queue" },
    { role: "courier", home: "courier-deliveries" },
];

describe("resolveGuardTarget", () => {
    for (const { role, home } of roles) {
        it(`${role}: foreign route redirects to role home`, () => {
            const result = resolveGuardTarget({
                authRequired: true,
                isAuthenticated: true,
                isGuestRoute: false,
                role,
                homeRouteName: home,
                routeName: "foreign-screen",
                allowedRouteNames: [home, "backoffice-not-found"],
            });
            assert.deepEqual(result, { type: "redirect", name: home });
        });

        it(`${role}: own route allowed`, () => {
            const result = resolveGuardTarget({
                authRequired: true,
                isAuthenticated: true,
                isGuestRoute: false,
                role,
                homeRouteName: home,
                routeName: home,
                allowedRouteNames: [home],
            });
            assert.deepEqual(result, { type: "allow" });
        });
    }

    it("guest → login when unauthenticated", () => {
        const result = resolveGuardTarget({
            authRequired: true,
            isAuthenticated: false,
            isGuestRoute: false,
            role: null,
            homeRouteName: null,
            routeName: "owner-dashboard",
            allowedRouteNames: [],
        });
        assert.equal(result.type, "redirect");
        assert.equal(result.name, "backoffice-login");
    });

    it("authenticated on guest route → role home", () => {
        const result = resolveGuardTarget({
            authRequired: true,
            isAuthenticated: true,
            isGuestRoute: true,
            role: "courier",
            homeRouteName: "courier-deliveries",
            routeName: "backoffice-login",
            allowedRouteNames: ["courier-deliveries"],
        });
        assert.deepEqual(result, { type: "redirect", name: "courier-deliveries" });
    });
});
