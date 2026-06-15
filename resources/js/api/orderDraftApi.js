import { httpClient } from "./httpClient";

export async function fetchStorefrontBootstrapRequest() {
    const response = await httpClient.get("/api/storefront/bootstrap");
    return response?.data ?? {};
}

export async function previewOrderDraftRequest(body) {
    const response = await httpClient.post("/api/order-drafts/preview", body);
    return response?.data ?? {};
}

export async function placeOrderRequest(body) {
    const response = await httpClient.post("/api/orders", body);
    return response?.data ?? {};
}
