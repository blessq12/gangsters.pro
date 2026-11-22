import { request, requestForm, requestMultipart } from "../http/transport.js";

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

/**
 * @param {{ offset?: number, limit?: number, q?: string }} [query]
 */
export function listSets(query = {}) {
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
    return request("GET", `/catalog/sets${qs ? `?${qs}` : ""}`);
}

/**
 * @param {{ offset?: number, limit?: number }} [query]
 */
export function listCategories(query = {}) {
    const params = new URLSearchParams();
    if (query.offset !== undefined) {
        params.set("offset", String(query.offset));
    }
    if (query.limit !== undefined) {
        params.set("limit", String(query.limit));
    }
    const qs = params.toString();
    return request("GET", `/catalog/categories${qs ? `?${qs}` : ""}`);
}

/**
 * @param {{ offset?: number, limit?: number, q?: string }} [query]
 */
export function listTags(query = {}) {
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
    return request("GET", `/catalog/tags${qs ? `?${qs}` : ""}`);
}

/** @param {number|string} id */
export function getProduct(id) {
    return request("GET", `/catalog/products/${id}`);
}

/** @param {number|string} id */
export function getSet(id) {
    return request("GET", `/catalog/sets/${id}`);
}

/** @param {number|string} id */
export function getCategory(id) {
    return request("GET", `/catalog/categories/${id}`);
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
        JSON.stringify((images.newImages ?? []).map((row) => ({
            focus_x: row.focus_x,
            focus_y: row.focus_y,
            sort_order: row.sort_order ?? null,
        }))),
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

/** @param {Record<string, unknown>} data */
export function storeCategory(data) {
    return request("POST", "/catalog/categories", data);
}

/** @param {number|string} id @param {Record<string, unknown>} data */
export function updateCategory(id, data) {
    return request("PUT", `/catalog/categories/${id}`, data);
}

/** @param {number|string} id */
export function deleteCategory(id) {
    return request("DELETE", `/catalog/categories/${id}`);
}

/** @param {number[]} ids */
export function reorderCategories(ids) {
    return request("PUT", "/catalog/categories/order", { ids });
}

/**
 * @param {number|string} id
 * @param {Array<{ item_id: number, sort_order?: number }>} items
 */
export function replaceCategoryItems(id, items) {
    return request("PUT", `/catalog/categories/${id}/items`, { items });
}

/**
 * @param {Record<string, unknown>} payload
 * @param {{
 *   newImages?: Array<{ file: File, focus_x: number, focus_y: number }>,
 *   existingImages?: Array<{ id: number, focus_x: number, focus_y: number, sort_order: number }>,
 *   deletedImageIds?: number[],
 * }} [images]
 */
export function storeSet(payload, images = {}) {
    return requestMultipart("POST", "/catalog/sets", buildCatalogItemFormData(payload, images));
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
export function updateSet(id, payload, images = {}) {
    const formData = buildCatalogItemFormData(payload, images);
    formData.append("_method", "PUT");
    return requestMultipart("POST", `/catalog/sets/${id}`, formData);
}

/** @param {number|string} id */
export function deleteSet(id) {
    return request("DELETE", `/catalog/sets/${id}`);
}

/**
 * @param {number|string} id
 * @param {Array<{ product_id: number, quantity: number }>} lines
 */
export function replaceSetLines(id, lines) {
    return request("PUT", `/catalog/sets/${id}/lines`, { lines });
}

/**
 * @param {number|string} itemId
 * @param {File} file
 * @param {{ focus_x?: number, focus_y?: number }} [focus]
 */
export function storeItemImage(itemId, file, focus = {}) {
    const formData = new FormData();
    formData.append("file", file);
    if (focus.focus_x != null && focus.focus_y != null) {
        formData.append("focus_x", String(focus.focus_x));
        formData.append("focus_y", String(focus.focus_y));
    }
    return requestForm(`/catalog/items/${itemId}/images`, formData);
}

/**
 * @param {number|string} itemId
 * @param {number|string} imageId
 * @param {Record<string, unknown>} data
 */
export function updateItemImage(itemId, imageId, data) {
    return request("PATCH", `/catalog/items/${itemId}/images/${imageId}`, data);
}

/** @param {number|string} itemId @param {number|string} imageId */
export function deleteItemImage(itemId, imageId) {
    return request("DELETE", `/catalog/items/${itemId}/images/${imageId}`);
}

/** @param {Record<string, unknown>} data */
export function storeTag(data) {
    return request("POST", "/catalog/tags", data);
}

/** @param {number|string} id @param {Record<string, unknown>} data */
export function updateTag(id, data) {
    return request("PUT", `/catalog/tags/${id}`, data);
}

/** @param {number|string} id */
export function deleteTag(id) {
    return request("DELETE", `/catalog/tags/${id}`);
}
