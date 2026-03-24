import { httpClient } from "./httpClient";

export async function fetchCatalogRequest() {
    const response = await httpClient.get("/api/catalog");
    return response?.data || {};
}

