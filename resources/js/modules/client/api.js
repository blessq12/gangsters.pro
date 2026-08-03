import { httpClient } from "../../platform/httpClient";
import { formatRuPhoneCanonical } from "../../platform/ruPhone";

export async function registerClientRequest(payload) {
    const response = await httpClient.post("/api/client/register", payload);
    return response.data;
}

export async function loginClientRequest(payload) {
    const response = await httpClient.post("/api/client/login", payload);
    return response.data;
}

export async function fetchClientProfileRequest() {
    const response = await httpClient.get("/api/client/profile");
    return response.data;
}

export async function updateClientProfileRequest(payload) {
    const response = await httpClient.patch("/api/client/profile", payload);
    return response.data;
}

export async function addClientAddressRequest(payload) {
    const response = await httpClient.post("/api/client/addresses", payload);
    return response.data;
}

export async function deleteClientAddressRequest(addressId) {
    const response = await httpClient.delete(`/api/client/addresses/${addressId}`);
    return response.data;
}

export async function fetchClientFavoritesRequest() {
    const response = await httpClient.get("/api/client/favorites");
    return response.data;
}

export async function toggleClientFavoriteRequest(productId, payload = {}) {
    const response = await httpClient.post(
        `/api/client/favorites/${encodeURIComponent(String(productId))}`,
        payload,
    );
    return response.data;
}

export async function removeClientFavoriteRequest(productId) {
    const response = await httpClient.delete(
        `/api/client/favorites/${encodeURIComponent(String(productId))}`,
    );
    return response.data;
}

export async function mergeGuestFavoritesRequest(payload) {
    const response = await httpClient.post("/api/client/favorites/merge", payload);
    return response.data;
}

export async function requestPasswordResetRequest(email) {
    const response = await httpClient.post("/api/client/forgot-password", {
        email,
    });
    return response.data;
}

export async function changePasswordWithTokenRequest({ token, password }) {
    const response = await httpClient.post("/api/client/change-password", {
        token,
        password,
    });
    return response.data;
}

export async function fetchOrdersRequest() {
    const response = await httpClient.get("/api/client/orders");
    return response.data;
}

export async function fetchRepeatableOrderLinesRequest(orderId) {
    const response = await httpClient.get(
        `/api/client/orders/${orderId}/repeatable-lines`,
    );
    return response.data;
}


export function buildRegisterClientPayload(data = {}) {
    return {
        name: data.name ?? "",
        phone: formatRuPhoneCanonical(data.phone ?? ""),
        email: data.email ?? "",
        birth_date: data.birth_date ?? null,
        password: data.password ?? null,
        consent_personal_data: Boolean(data.consent_personal_data),
        consent_marketing: Boolean(data.consent_marketing),
    };
}

/** Передай ровно один идентификатор: phone или email (второй — null). */
export function buildLoginClientPayload(data = {}) {
    const rawPhone = data.phone;
    const formatted =
        rawPhone == null || rawPhone === ""
            ? ""
            : formatRuPhoneCanonical(rawPhone);
    return {
        phone: formatted || null,
        email: data.email ?? null,
        password: data.password ?? "",
    };
}

export function buildUpdateClientProfilePayload(data = {}) {
    const payload = {};
    if ("name" in data) payload.name = data.name;
    if ("phone" in data)
        payload.phone = formatRuPhoneCanonical(data.phone ?? "");
    if ("email" in data) payload.email = data.email ?? null;
    if ("birth_date" in data) payload.birth_date = data.birth_date ?? null;
    if ("consent_personal_data" in data)
        payload.consent_personal_data = Boolean(data.consent_personal_data);
    if ("consent_marketing" in data)
        payload.consent_marketing = Boolean(data.consent_marketing);
    return payload;
}

export function buildClientAddressPayload(data = {}) {
    return {
        type: data.type ?? undefined,
        title: data.title ?? null,
        street: data.street ?? "",
        house: data.house ?? "",
        entrance: data.entrance ?? null,
        apartment: data.apartment ?? null,
        comment: data.comment ?? null,
        make_default:
            "make_default" in data ? Boolean(data.make_default) : undefined,
    };
}

export function buildToggleClientFavoritePayload(product = {}) {
    return {
        name: product.name ?? "",
        price: Number(product.price) || 0,
        weight: product.weight ?? null,
    };
}

export function buildMergeGuestFavoritesPayload(items = []) {
    if (!Array.isArray(items)) {
        return { items: [] };
    }

    return {
        items: items
            .map((item) => {
                const productId = Number(item?.productId ?? item?.productSnapshot?.id) || 0;
                if (productId <= 0) {
                    return null;
                }

                const snapshot = item?.productSnapshot ?? {};
                return {
                    product_id: productId,
                    product_name: snapshot.name ? String(snapshot.name) : null,
                    price_rub: Number(snapshot.price) || 0,
                    weight: snapshot.weight ?? null,
                };
            })
            .filter(Boolean),
    };
}
