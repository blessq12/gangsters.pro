import { httpClient } from "./httpClient";

export async function fetchOrdersRequest() {
    const response = await httpClient.get("/api/order");
    return response.data;
}
