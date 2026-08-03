import { httpClient } from "./httpClient";

export async function registerClientRequest(payload) {
    const response = await httpClient.post("/api/client/register", payload);
    return response.data;
}

export async function loginClientRequest(payload) {
    const response = await httpClient.post("/api/client/login", payload);
    return response.data;
}

export async function fetchClientProfileRequest() {
    const response = await httpClient.get("/api/client/profile");
    return response.data;
}

export async function updateClientProfileRequest(payload) {
    const response = await httpClient.patch("/api/client/profile", payload);
    return response.data;
}

export async function addClientAddressRequest(payload) {
    const response = await httpClient.post("/api/client/addresses", payload);
    return response.data;
}

export async function deleteClientAddressRequest(addressId) {
    const response = await httpClient.delete(`/api/client/addresses/${addressId}`);
    return response.data;
}

export async function fetchClientFavoritesRequest() {
    const response = await httpClient.get("/api/client/favorites");
    return response.data;
}

export async function toggleClientFavoriteRequest(productId, payload = {}) {
    const response = await httpClient.post(
        `/api/client/favorites/${encodeURIComponent(String(productId))}`,
        payload,
    );
    return response.data;
}

export async function removeClientFavoriteRequest(productId) {
    const response = await httpClient.delete(
        `/api/client/favorites/${encodeURIComponent(String(productId))}`,
    );
    return response.data;
}

export async function mergeGuestFavoritesRequest(payload) {
    const response = await httpClient.post("/api/client/favorites/merge", payload);
    return response.data;
}

export async function requestPasswordResetRequest(email) {
    const response = await httpClient.post("/api/client/forgot-password", {
        email,
    });
    return response.data;
}

export async function changePasswordWithTokenRequest({ token, password }) {
    const response = await httpClient.post("/api/client/change-password", {
        token,
        password,
    });
    return response.data;
}

export async function fetchOrdersRequest() {
    const response = await httpClient.get("/api/client/orders");
    return response.data;
}

export async function fetchRepeatableOrderLinesRequest(orderId) {
    const response = await httpClient.get(
        `/api/client/orders/${orderId}/repeatable-lines`,
    );
    return response.data;
}
