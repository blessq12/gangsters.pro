import { http } from "../http/transport";

function unwrapData(response) {
    const payload = response?.data;
    if (payload && typeof payload === "object" && "data" in payload) {
        return payload.data ?? {};
    }

    return payload ?? {};
}

/** POST /api/public/order/quote */
export async function quoteOrderRequest(body) {
    const response = await http.post("/order/quote", body);
    return unwrapData(response);
}

/** POST /api/public/order */
export async function placeOrderRequest(body) {
    const response = await http.post("/order", body);
    return unwrapData(response);
}
