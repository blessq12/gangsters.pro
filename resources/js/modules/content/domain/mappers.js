function safeTrim(value) {
    if (value == null) return "";
    if (typeof value === "string") return value.trim();
    return String(value).trim();
}

const VALID_DAYS = ["mon", "tue", "wed", "thu", "fri", "sat", "sun"];

/**
 * @param {unknown} raw
 * @returns {Record<string, { work: string|null, is_day_off: boolean }>}
 */
function normalizeScheduleObject(raw) {
    /** @type {Record<string, { work: string|null, is_day_off: boolean }>} */
    const out = {};
    for (const day of VALID_DAYS) {
        out[day] = { work: null, is_day_off: false };
    }

    if (!raw || typeof raw !== "object" || Array.isArray(raw)) {
        return out;
    }

    for (const day of VALID_DAYS) {
        const row = raw[day];
        if (!row || typeof row !== "object") continue;
        const isDayOff =
            row.is_day_off === true ||
            row.is_day_off === 1 ||
            row.is_day_off === "1";
        const workRaw = row.work;
        const work =
            typeof workRaw === "string" && workRaw.trim() !== ""
                ? workRaw.trim()
                : null;
        out[day] = { work, is_day_off: isDayOff };
    }

    return out;
}

/**
 * @param {unknown} raw
 * @returns {{ telegram: string|null, vk: string|null, inst: string|null, site_url: string|null, whatsapp: string|null }}
 */
function normalizeSocials(raw) {
    const data = raw && typeof raw === "object" && !Array.isArray(raw) ? raw : {};
    return {
        telegram: safeTrim(data.telegram) || null,
        vk: safeTrim(data.vk) || null,
        inst: safeTrim(data.inst) || null,
        site_url: safeTrim(data.site_url) || null,
        whatsapp: safeTrim(data.whatsapp) || null,
    };
}

/**
 * @param {unknown} apiProfile
 * @returns {object|null}
 */
export function normalizeCompanyProfile(apiProfile) {
    if (!apiProfile || typeof apiProfile !== "object") {
        return null;
    }

    const name = safeTrim(apiProfile.name);
    if (!name) {
        return null;
    }

    return {
        id: apiProfile.id ?? null,
        name,
        description: safeTrim(apiProfile.description) || null,
        phone: safeTrim(apiProfile.phone) || null,
        email: safeTrim(apiProfile.email) || null,
        socials: normalizeSocials(apiProfile.socials),
        schedule: normalizeScheduleObject(apiProfile.schedule),
    };
}

/**
 * @param {unknown} apiLegal
 * @returns {object|null}
 */
export function normalizeCompanyLegal(apiLegal) {
    if (!apiLegal || typeof apiLegal !== "object") {
        return null;
    }

    return {
        id: apiLegal.id ?? null,
        company_id: apiLegal.company_id ?? null,
        full_name: safeTrim(apiLegal.full_name) || null,
        inn: safeTrim(apiLegal.inn) || null,
        ogrn: safeTrim(apiLegal.ogrn) || null,
    };
}

/**
 * @param {unknown} apiDocument
 * @returns {object|null}
 */
export function normalizeCompanyDocument(apiDocument) {
    if (!apiDocument || typeof apiDocument !== "object") {
        return null;
    }

    const slug = safeTrim(apiDocument.slug);
    const name = safeTrim(apiDocument.name);
    if (!slug || !name) {
        return null;
    }

    const content = apiDocument.content;
    const normalizedContent =
        typeof content === "string" && content.trim() !== ""
            ? content
            : null;

    return {
        id: apiDocument.id ?? null,
        slug,
        name,
        content: normalizedContent,
    };
}

/**
 * Плоское представление для утилит отображения (companyDeliveryFacts, карта).
 * @param {object|null|undefined} delivery
 * @returns {object|null}
 */
export function toDeliveryFactsView(delivery) {
    if (!delivery || typeof delivery !== "object") {
        return null;
    }

    const settings = delivery.settings || {};
    const zone = delivery.zone || {};
    const kitchen = zone.kitchen_address || {};

    return {
        min_order_amount_kopecks: settings.min_order_amount_kopecks ?? null,
        delivery_fee_kopecks: settings.delivery_fee_kopecks ?? null,
        outside_zone_delivery_fee_kopecks:
            settings.outside_zone_delivery_fee_kopecks ?? null,
        average_delivery_time_minutes:
            settings.average_delivery_time_minutes ?? null,
        city: kitchen.city,
        street: kitchen.street,
        house: kitchen.house,
        address_comment: kitchen.comment,
        kitchen_latitude: zone.kitchen_latitude ?? null,
        kitchen_longitude: zone.kitchen_longitude ?? null,
        delivery_zone_geojson: zone.delivery_zone_geojson ?? null,
    };
}
