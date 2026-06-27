import { httpClient } from "./httpClient";

export async function fetchStorefrontBootstrapCriticalRequest() {
    const response = await httpClient.get("/api/storefront/bootstrap/critical");
    return response?.data ?? {};
}

export async function fetchStorefrontBootstrapDeferredRequest() {
    const response = await httpClient.get("/api/storefront/bootstrap/deferred");
    return response?.data ?? {};
}

export async function fetchStorefrontBootstrapRequest() {
    const response = await httpClient.get("/api/storefront/bootstrap");
    return response?.data ?? {};
}
