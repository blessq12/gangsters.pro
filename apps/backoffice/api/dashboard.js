import { request } from "../http/transport.js";

/**
 * @param {{
 *   from?: string,
 *   to?: string,
 *   branch_id?: number|string,
 * }} [query]
 */
export function getDashboardSummary(query = {}) {
    const params = new URLSearchParams();
    if (query.from) {
        params.set("from", query.from);
    }
    if (query.to) {
        params.set("to", query.to);
    }
    if (query.branch_id !== undefined && query.branch_id !== null && query.branch_id !== "") {
        params.set("branch_id", String(query.branch_id));
    }
    const qs = params.toString();
    return request("GET", `/dashboard/summary${qs ? `?${qs}` : ""}`);
}
