export function readBackofficeAuthRequired() {
    const value = window.__BACKOFFICE__?.authRequired;
    return value === true || value === "true" || value === 1 || value === "1";
}

export function homeRoute() {
    return { name: "backoffice-home" };
}
