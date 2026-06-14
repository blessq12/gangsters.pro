import { httpClient } from "./httpClient";

export async function fetchSystemBannersRequest() {
    const response = await httpClient.get("/api/system/banners");
    return response.data;
}

export async function fetchSystemPromotionsRequest() {
    const response = await httpClient.get("/api/system/promotions");
    return response.data;
}
