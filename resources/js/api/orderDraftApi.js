import { httpClient } from "./httpClient";

function unwrapData(response) {
    const payload = response?.data;
    if (payload && typeof payload === "object" && "data" in payload) {
        return payload.data ?? {};
    }

    return payload ?? {};
}

export async function quoteOrderRequest(body) {
    const response = await httpClient.post("/api/order/quote", body);
    return unwrapData(response);
}

export async function placeOrderRequest(body) {
    const response = await httpClient.post("/api/order", body);
    return unwrapData(response);
}

/** @deprecated используй quoteOrderRequest */
export async function previewOrderDraftRequest(body) {
    return quoteOrderRequest(body);
}
