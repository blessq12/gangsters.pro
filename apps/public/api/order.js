import { http } from "../http/transport";

function unwrapData(response) {
    const payload = response?.data;
    if (payload && typeof payload === "object" && "data" in payload) {
        return payload.data ?? {};
    }

    return payload ?? {};
}

/** GET /api/public/checkout/context — всё для локального расчёта корзины филиала. */
export async function fetchCheckoutContextRequest(branchId) {
    const response = await http.get("/checkout/context", {
        params: { branch_id: branchId },
    });
    return unwrapData(response);
}

/** POST /api/public/order — слепок намерения клиента. */
export async function placeOrderRequest(body) {
    const response = await http.post("/order", body);
    return unwrapData(response);
}
