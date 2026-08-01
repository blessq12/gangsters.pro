import { httpClient } from "./httpClient";

export async function fetchContentBootstrapRequest() {
    const response = await httpClient.get("/api/content/bootstrap");
    return response?.data ?? {};
}
