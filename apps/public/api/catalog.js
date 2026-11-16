import { http } from "../http/transport";

/** GET /api/public/catalog */
export async function fetchCatalog() {
    const response = await http.get("/catalog");
    return response.data;
}
