import { normalizeRuPhoneDigits } from "../validation/ruPhone";

export function buildRegisterClientPayload(data = {}) {
    return {
        name: data.name ?? "",
        phone: normalizeRuPhoneDigits(data.phone ?? ""),
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
    const digits =
        rawPhone == null || rawPhone === ""
            ? ""
            : normalizeRuPhoneDigits(rawPhone);
    return {
        phone: digits || null,
        email: data.email ?? null,
        password: data.password ?? "",
    };
}

export function buildUpdateClientProfilePayload(data = {}) {
    const payload = {};
    if ("name" in data) payload.name = data.name;
    if ("phone" in data)
        payload.phone = normalizeRuPhoneDigits(data.phone ?? "");
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

