import { request, requestMultipart } from "../http/transport.js";

export function getMarketing() {
    return request("GET", "/marketing");
}

/**
 * @param {string} path
 * @param {{ offset?: number, limit?: number }} [query]
 */
function withPage(path, query = {}) {
    const params = new URLSearchParams();
    if (query.offset !== undefined) {
        params.set("offset", String(query.offset));
    }
    if (query.limit !== undefined) {
        params.set("limit", String(query.limit));
    }
    const qs = params.toString();
    return request("GET", `${path}${qs ? `?${qs}` : ""}`);
}

/** @param {{ offset?: number, limit?: number }} [query] */
export function listBanners(query = {}) {
    return withPage("/marketing/banners", query);
}

/** @param {number|string} id */
export function getBanner(id) {
    return request("GET", `/marketing/banners/${id}`);
}

/** @param {{ offset?: number, limit?: number }} [query] */
export function listPromotions(query = {}) {
    return withPage("/marketing/promotions", query);
}

/** @param {number|string} id */
export function getPromotion(id) {
    return request("GET", `/marketing/promotions/${id}`);
}

/**
 * @param {{ is_active?: boolean }} payload
 * @param {{ desktop?: File|null, mobile?: File|null }} files
 */
function buildBannerFormData(payload, files = {}) {
    const formData = new FormData();
    if (payload.is_active !== undefined) {
        formData.append("is_active", payload.is_active ? "1" : "0");
    }
    if (files.desktop instanceof File) {
        formData.append("image_desktop", files.desktop);
    }
    if (files.mobile instanceof File) {
        formData.append("image_mobile", files.mobile);
    }
    return formData;
}

/**
 * @param {{ is_active?: boolean }} payload
 * @param {{ desktop?: File|null, mobile?: File|null }} [files]
 */
export function storeBanner(payload, files = {}) {
    return requestMultipart("POST", "/marketing/banners", buildBannerFormData(payload, files));
}

/**
 * @param {number|string} id
 * @param {{ is_active?: boolean }} payload
 * @param {{ desktop?: File|null, mobile?: File|null }} [files]
 */
export function updateBanner(id, payload, files = {}) {
    const formData = buildBannerFormData(payload, files);
    formData.append("_method", "PUT");
    return requestMultipart("POST", `/marketing/banners/${id}`, formData);
}

/**
 * @param {{
 *   title: string,
 *   body?: string|null,
 *   is_active?: boolean,
 *   remove_image?: boolean,
 * }} payload
 * @param {{ image?: File|null }} [files]
 */
function buildPromotionFormData(payload, files = {}) {
    const formData = new FormData();
    formData.append("title", payload.title ?? "");
    if (payload.body != null) {
        formData.append("body", payload.body);
    }
    if (payload.is_active !== undefined) {
        formData.append("is_active", payload.is_active ? "1" : "0");
    }
    if (payload.remove_image) {
        formData.append("remove_image", "1");
    }
    if (files.image instanceof File) {
        formData.append("image", files.image);
    }
    return formData;
}

/**
 * @param {{
 *   title: string,
 *   body?: string|null,
 *   is_active?: boolean,
 * }} payload
 * @param {{ image?: File|null }} [files]
 */
export function storePromotion(payload, files = {}) {
    return requestMultipart(
        "POST",
        "/marketing/promotions",
        buildPromotionFormData(payload, files),
    );
}

/**
 * @param {number|string} id
 * @param {{
 *   title: string,
 *   body?: string|null,
 *   is_active?: boolean,
 *   remove_image?: boolean,
 * }} payload
 * @param {{ image?: File|null }} [files]
 */
export function updatePromotion(id, payload, files = {}) {
    const formData = buildPromotionFormData(payload, files);
    formData.append("_method", "PUT");
    return requestMultipart("POST", `/marketing/promotions/${id}`, formData);
}

/** @param {Record<string, unknown>} data */
export function saveDeliveryConditions(data) {
    return request("PUT", "/marketing/delivery-conditions", data);
}
