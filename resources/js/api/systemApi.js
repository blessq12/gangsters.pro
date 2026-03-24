import { httpClient } from "./httpClient";

export async function fetchSystemBannersRequest() {
    const response = await httpClient.get("/api/system/banners");
    return response.data;
}

export async function fetchSystemPromotionsRequest() {
    const response = await httpClient.get("/api/system/promotions");
    return response.data;
}

export async function fetchSystemCompanyRequest() {
    const response = await httpClient.get("/api/system/company");
    return response.data;
}

export async function fetchSystemCompanyLegalRequest() {
    const response = await httpClient.get("/api/system/company-legal");
    return response.data;
}

export async function fetchSystemDocumentsRequest() {
    const response = await httpClient.get("/api/system/documents");
    return response.data;
}

