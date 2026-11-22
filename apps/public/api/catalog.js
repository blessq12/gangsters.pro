import { http } from "../http/transport";

/** GET /api/public/catalog?branch_id= */
export async function fetchCatalog(branchId) {
    const response = await http.get("/catalog", {
        params: { branch_id: branchId },
    });
    return response.data;
}
