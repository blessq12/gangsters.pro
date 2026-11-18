import { http } from "../http/transport";

/** GET /api/public/content */
export async function fetchContent() {
    const response = await http.get("/content");
    return response.data;
}
