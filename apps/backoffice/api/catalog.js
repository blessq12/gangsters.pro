import { request, requestMultipart } from "../http/transport.js";

export function getCatalog() {
    return request("GET", "/catalog");
}

/**
 * @param {{ offset?: number, limit?: number, q?: string }} [query]
 */
export function listProducts(query = {}) {
    const params = new URLSearchParams();
    if (query.offset !== undefined) {
        params.set("offset", String(query.offset));
    }
    if (query.limit !== undefined) {
        params.set("limit", String(query.limit));
    }
    if (query.q) {
        params.set("q", query.q);
    }
    const qs = params.toString();
    return request("GET", `/catalog/products${qs ? `?${qs}` : ""}`);
}

/** @param {number|string} id */
export function getProduct(id) {
    return request("GET", `/catalog/products/${id}`);
}

/**
 * @param {Record<string, unknown>} payload
 * @param {{
 *   newImages?: Array<{ file: File, focus_x: number, focus_y: number, sort_order?: number }>,
 *   existingImages?: Array<{ id: number, focus_x: number, focus_y: number, sort_order: number }>,
 *   deletedImageIds?: number[],
 * }} [images]
 */
function buildCatalogItemFormData(payload, images = {}) {
    const formData = new FormData();
    formData.append("payload", JSON.stringify(payload));
    formData.append(
        "images_meta",
        JSON.stringify(
            (images.newImages ?? []).map((row) => ({
                focus_x: row.focus_x,
                focus_y: row.focus_y,
                sort_order: row.sort_order ?? null,
            })),
        ),
    );
    formData.append("existing_images", JSON.stringify(images.existingImages ?? []));
    formData.append("deleted_image_ids", JSON.stringify(images.deletedImageIds ?? []));
    for (const row of images.newImages ?? []) {
        formData.append("images[]", row.file);
    }
    return formData;
}

/**
 * @param {Record<string, unknown>} payload
 * @param {{
 *   newImages?: Array<{ file: File, focus_x: number, focus_y: number }>,
 *   existingImages?: Array<{ id: number, focus_x: number, focus_y: number, sort_order: number }>,
 *   deletedImageIds?: number[],
 * }} [images]
 */
export function storeProduct(payload, images = {}) {
    return requestMultipart("POST", "/catalog/products", buildCatalogItemFormData(payload, images));
}

/**
 * @param {number|string} id
 * @param {Record<string, unknown>} payload
 * @param {{
 *   newImages?: Array<{ file: File, focus_x: number, focus_y: number }>,
 *   existingImages?: Array<{ id: number, focus_x: number, focus_y: number, sort_order: number }>,
 *   deletedImageIds?: number[],
 * }} [images]
 */
export function updateProduct(id, payload, images = {}) {
    // PHP парсит multipart только на POST; Laravel принимает _method=PUT.
    const formData = buildCatalogItemFormData(payload, images);
    formData.append("_method", "PUT");
    return requestMultipart("POST", `/catalog/products/${id}`, formData);
}

/** @param {number|string} id */
export function deleteProduct(id) {
    return request("DELETE", `/catalog/products/${id}`);
}
