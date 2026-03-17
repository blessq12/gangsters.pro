import axios from "axios";

export function buildRegisterClientPayload(data = {}) {
    return {
        name: data.name ?? "",
        phone: data.phone ?? "",
        email: data.email ?? null,
        birth_date: data.birth_date ?? null,
        password: data.password ?? null,
        consent_personal_data: Boolean(data.consent_personal_data),
        consent_marketing: Boolean(data.consent_marketing),
    };
}

export function buildLoginClientPayload(data = {}) {
    return {
        phone: data.phone ?? null,
        email: data.email ?? null,
        password: data.password ?? "",
    };
}

export function buildUpdateClientProfilePayload(data = {}) {
    const payload = {};
    if ("name" in data) payload.name = data.name;
    if ("phone" in data) payload.phone = data.phone;
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

