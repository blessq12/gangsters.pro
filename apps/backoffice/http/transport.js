import { getBackofficeAuthToken, persistBackofficeAuthToken } from "../shell/authToken";

const API_BASE = "/api/backoffice";

/** @type {null | (() => void)} */
let unauthorizedHandler = null;

export function setUnauthorizedHandler(handler) {
    unauthorizedHandler = typeof handler === "function" ? handler : null;
}

function messageFrom(payload) {
    if (typeof payload?.message === "string" && payload.message !== "") {
        return payload.message;
    }
    const errors = payload?.errors;
    if (errors && typeof errors === "object") {
        const first = Object.values(errors)[0];
        if (Array.isArray(first) && typeof first[0] === "string") {
            return first[0];
        }
    }
    return "Не удалось связаться с сервером.";
}

function buildUrl(path) {
    if (/^https?:\/\//i.test(path)) {
        return path;
    }
    const normalized = path.startsWith("/") ? path : `/${path}`;
    return `${API_BASE}${normalized}`;
}

function buildHeaders(extra = {}) {
    const headers = {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
        ...extra,
    };
    const token = getBackofficeAuthToken();
    if (token) {
        headers.Authorization = `Bearer ${token}`;
    }
    return headers;
}

function handleHttpError(status, payload) {
    if (status === 401) {
        persistBackofficeAuthToken(null);
        unauthorizedHandler?.();
    }
    if (status === 403) {
        throw new Error(payload?.message || "Forbidden.");
    }
    throw new Error(messageFrom(payload));
}

/**
 * HTTP transport for the backoffice SPA (own contour, base /api/backoffice).
 */
export async function request(method, path, body) {
    const headers = buildHeaders(
        body !== undefined ? { "Content-Type": "application/json" } : {},
    );

    const response = await fetch(buildUrl(path), {
        method,
        headers,
        body: body !== undefined ? JSON.stringify(body) : undefined,
    });
    const payload = await response.json().catch(() => ({}));
    if (!response.ok) {
        handleHttpError(response.status, payload);
    }
    return payload;
}

export async function requestForm(path, formData) {
    return requestMultipart("POST", path, formData);
}

/**
 * Multipart request (do not set Content-Type — browser adds boundary).
 * @param {string} method
 * @param {string} path
 * @param {FormData} formData
 */
export async function requestMultipart(method, path, formData) {
    const response = await fetch(buildUrl(path), {
        method,
        headers: buildHeaders(),
        body: formData,
    });
    const payload = await response.json().catch(() => ({}));
    if (!response.ok) {
        handleHttpError(response.status, payload);
    }
    return payload;
}
