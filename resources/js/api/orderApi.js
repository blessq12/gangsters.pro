import { httpClient } from "./httpClient";

function unwrapData(response) {
    const payload = response?.data;
    if (payload && typeof payload === "object" && "data" in payload) {
        return payload.data ?? {};
    }

    return payload ?? {};
}

/** POST /api/order/quote */
export async function quoteOrderRequest(body) {
    const response = await httpClient.post("/api/order/quote", body);
    return unwrapData(response);
}

/** POST /api/order */
export async function placeOrderRequest(body) {
    const response = await httpClient.post("/api/order", body);
    return unwrapData(response);
}
