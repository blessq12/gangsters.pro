import { listProducts } from "../../../api/catalog.js";

const PAGE_LIMIT = 100;

/**
 * Все товары справочника (постранично до total).
 * @returns {Promise<Array<{ id: number, name: string, status?: string }>>}
 */
export async function fetchAllProducts() {
    const result = [];
    let total = 0;
    do {
        const payload = await listProducts({ offset: result.length, limit: PAGE_LIMIT });
        const items = Array.isArray(payload?.items) ? payload.items : [];
        total = Number(payload?.total ?? 0);
        result.push(...items);
        if (items.length === 0) {
            break;
        }
    } while (result.length < total);
    return result;
}
