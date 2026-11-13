export function readBackofficeAuthRequired() {
    const value = window.__BACKOFFICE__?.authRequired;
    return value === true || value === "true" || value === 1 || value === "1";
}

export const ROLE_HOME = {
    owner: { name: "owner-order" },
    administrator: { name: "administrator-order" },
    kitchen: { name: "production-production" },
    courier: { name: "courier-delivery" },
};

export function homeRouteForRole(role) {
    return ROLE_HOME[role] ?? { name: "backoffice-login" };
}
