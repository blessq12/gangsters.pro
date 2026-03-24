import { httpClient } from "./httpClient";

export async function fetchOrdersRequest() {
    const response = await httpClient.get("/api/order");
    return response.data;
}

export async function createOrderRequest(payload) {
    const response = await httpClient.post("/api/order", payload);
    return response.data;
}

