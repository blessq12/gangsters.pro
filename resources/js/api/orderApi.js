import { httpClient } from "./httpClient";

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
