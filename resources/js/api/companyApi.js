import { httpClient } from "./httpClient";

export async function fetchCompanyMainRequest() {
    const response = await httpClient.get("/api/company/main");
    return response.data;
}

export async function fetchCompanyLegalsRequest() {
    const response = await httpClient.get("/api/company/legals");
    return response.data;
}

export async function fetchCompanyDocumentsRequest() {
    const response = await httpClient.get("/api/company/documents");
    return response.data;
}
