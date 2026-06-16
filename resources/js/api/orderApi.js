import { httpClient } from "./httpClient";

export async function fetchOrdersRequest() {
    const response = await httpClient.get("/api/order");
    return response.data;
}

export async function fetchRepeatableOrderLinesRequest(orderId) {
    const response = await httpClient.get(`/api/order/${orderId}/repeatable-lines`);
    return response.data;
}
