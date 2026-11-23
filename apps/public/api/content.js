import { http } from "../http/transport";

/** GET /api/public/company → { company, legal, documents, branch, branches } */
export async function fetchCompany() {
    const response = await http.get("/company");
    return response.data;
}

/** GET /api/public/marketing → { banners, promotions, delivery_conditions_text } */
export async function fetchMarketing() {
    const response = await http.get("/marketing");
    return response.data;
}

/** GET /api/public/delivery?branch_id= */
export async function fetchDelivery(branchId) {
    const response = await http.get("/delivery", {
        params: { branch_id: branchId },
    });
    return response.data;
}
