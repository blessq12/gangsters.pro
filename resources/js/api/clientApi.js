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
