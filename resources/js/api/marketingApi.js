import { httpClient } from "./httpClient";

export async function fetchMarketingBannersRequest() {
    const response = await httpClient.get("/api/marketing/banners");
    return response.data;
}

export async function fetchMarketingPromotionsRequest() {
    const response = await httpClient.get("/api/marketing/promotions");
    return response.data;
}
