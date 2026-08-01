import { httpClient } from "./httpClient";

export async function fetchAppBootstrapCriticalRequest() {
    const response = await httpClient.get("/api/bootstrap/critical");
    return response?.data ?? {};
}

export async function fetchAppBootstrapDeferredRequest() {
    const response = await httpClient.get("/api/bootstrap/deferred");
    return response?.data ?? {};
}

export async function fetchAppBootstrapRequest() {
    const response = await httpClient.get("/api/bootstrap");
    return response?.data ?? {};
}
