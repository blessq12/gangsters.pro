/**
 * Pure shell guard decision (no Vue). Used by router and unit tests.
 *
 * @param {object} input
 * @param {boolean} input.authRequired
 * @param {boolean} input.isAuthenticated
 * @param {boolean} input.isGuestRoute
 * @param {string|null} input.role
 * @param {string|null} input.homeRouteName
 * @param {string|null} input.routeName
 * @param {Set<string>|string[]} input.allowedRouteNames shell + active role routes
 * @returns {{ type: 'allow' } | { type: 'redirect', name: string, query?: Record<string, string> }}
 */
export function resolveGuardTarget(input) {
    const {
        authRequired,
        isAuthenticated,
        isGuestRoute,
        homeRouteName,
        routeName,
        allowedRouteNames,
    } = input;

    if (!authRequired) {
        return { type: "allow" };
    }

    if (isGuestRoute) {
        if (isAuthenticated && homeRouteName) {
            return { type: "redirect", name: homeRouteName };
        }
        return { type: "allow" };
    }

    if (!isAuthenticated) {
        return {
            type: "redirect",
            name: "backoffice-login",
            query: routeName ? { redirect: String(routeName) } : undefined,
        };
    }

    if (!homeRouteName) {
        return { type: "redirect", name: "backoffice-login" };
    }

    const allowed = allowedRouteNames instanceof Set
        ? allowedRouteNames
        : new Set(allowedRouteNames);

    if (routeName && !allowed.has(routeName)) {
        return { type: "redirect", name: homeRouteName };
    }

    return { type: "allow" };
}
