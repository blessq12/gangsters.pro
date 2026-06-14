import { httpClient } from "./httpClient";

function unwrap(response) {
    return response.data?.data ?? response.data;
}

export async function createCheckoutRequest() {
    const response = await httpClient.post("/api/checkout");
    return unwrap(response);
}

export async function updateCheckoutCartRequest(checkoutId, payload) {
    const response = await httpClient.patch(
        `/api/checkout/${encodeURIComponent(String(checkoutId))}/cart`,
        payload,
    );
    return unwrap(response);
}

export async function setCheckoutClientRequest(checkoutId, payload) {
    const response = await httpClient.patch(
        `/api/checkout/${encodeURIComponent(String(checkoutId))}/client`,
        payload,
    );
    return unwrap(response);
}

export async function setCheckoutDeliveryRequest(checkoutId, payload) {
    const response = await httpClient.patch(
        `/api/checkout/${encodeURIComponent(String(checkoutId))}/delivery`,
        payload,
    );
    return unwrap(response);
}

export async function setCheckoutPaymentRequest(checkoutId, payload) {
    const response = await httpClient.patch(
        `/api/checkout/${encodeURIComponent(String(checkoutId))}/payment`,
        payload,
    );
    return unwrap(response);
}

export async function confirmCheckoutRequest(checkoutId) {
    const response = await httpClient.post(
        `/api/checkout/${encodeURIComponent(String(checkoutId))}/confirm`,
    );
    return unwrap(response);
}
