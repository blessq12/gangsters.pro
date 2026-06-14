import { httpClient } from "./httpClient";

export async function fetchDeliveryRequest() {
    const response = await httpClient.get("/api/delivery");
    return response.data;
}
