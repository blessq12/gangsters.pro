import { httpClient } from "./httpClient";

export async function fetchShoppingStateRequest() {
    const response = await httpClient.get("/api/shopping/state");
    return response.data?.data ?? response.data;
}

export async function upsertCartLineRequest(payload) {
    const response = await httpClient.post("/api/shopping/cart/items", payload);
    return response.data?.data ?? response.data;
}

export async function removeCartLineRequest(productId) {
    const response = await httpClient.delete(
        `/api/shopping/cart/items/${encodeURIComponent(String(productId))}`,
    );
    return response.data?.data ?? response.data;
}

export async function clearCartRequest() {
    const response = await httpClient.delete("/api/shopping/cart");
    return response.data?.data ?? response.data;
}

export async function recalculateCartRequest() {
    const response = await httpClient.post("/api/shopping/cart/recalculate");
    return response.data?.data ?? response.data;
}

export async function toggleFavoriteRequest(productId) {
    const response = await httpClient.post(
        `/api/shopping/favorites/${encodeURIComponent(String(productId))}`,
    );
    return response.data?.data ?? response.data;
}

export async function removeFavoriteRequest(productId) {
    const response = await httpClient.delete(
        `/api/shopping/favorites/${encodeURIComponent(String(productId))}`,
    );
    return response.data?.data ?? response.data;
}

export async function patchCheckoutDraftRequest(payload) {
    const response = await httpClient.patch("/api/shopping/checkout-draft", payload);
    return response.data?.data ?? response.data;
}

export async function mergeShoppingSessionRequest() {
    const response = await httpClient.post("/api/shopping/merge");
    return response.data?.data ?? response.data;
}

export async function migrateLocalShoppingRequest(payload) {
    const response = await httpClient.post("/api/shopping/migrate", payload);
    return response.data?.data ?? response.data;
}

export async function logoutShoppingSessionRequest() {
    const response = await httpClient.post("/api/shopping/logout");
    return response.data?.data ?? response.data;
}
