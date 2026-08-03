function safeTrim(value) {
    if (value == null) return "";
    if (typeof value === "string") return value.trim();
    return String(value).trim();
}

const VALID_DAYS = new Set(["mon", "tue", "wed", "thu", "fri", "sat", "sun"]);

/**
 * @param {unknown} raw
 * @returns {Array<{ day: string, work: string|null, is_day_off: boolean }>}
 */
function normalizeWorkSchedule(raw) {
    if (!Array.isArray(raw)) {
        return [];
    }

    return raw
        .map((row) => {
            if (!row || typeof row !== "object") {
                return null;
            }

            const day = safeTrim(row.day).toLowerCase();
            if (!VALID_DAYS.has(day)) {
                return null;
            }

            const isDayOff =
                row.is_day_off === true ||
                row.is_day_off === 1 ||
                row.is_day_off === "1";

            const workRaw = row.work;
            const work =
                typeof workRaw === "string" && workRaw.trim() !== ""
                    ? workRaw.trim()
                    : null;

            return {
                day,
                work,
                is_day_off: isDayOff,
            };
        })
        .filter(Boolean);
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
        brand_name: safeTrim(apiProfile.brand_name) || null,
        description: safeTrim(apiProfile.description) || null,
        tagline: safeTrim(apiProfile.tagline) || null,
        phone: safeTrim(apiProfile.phone) || null,
        phone_additional: safeTrim(apiProfile.phone_additional) || null,
        support_phone: safeTrim(apiProfile.support_phone) || null,
        whatsapp_phone: safeTrim(apiProfile.whatsapp_phone) || null,
        email_address: safeTrim(apiProfile.email_address) || null,
        public_email: safeTrim(apiProfile.public_email) || null,
        work_hours: safeTrim(apiProfile.work_hours) || null,
        work_schedule: normalizeWorkSchedule(apiProfile.work_schedule),
        logo: safeTrim(apiProfile.logo) || null,
        telegram: safeTrim(apiProfile.telegram) || null,
        site_url: safeTrim(apiProfile.site_url) || null,
        vk: safeTrim(apiProfile.vk) || null,
        inst: safeTrim(apiProfile.inst) || null,
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
        short_name: safeTrim(apiLegal.short_name) || null,
        legal_form: safeTrim(apiLegal.legal_form) || null,
        legal_email: safeTrim(apiLegal.legal_email) || null,
        contracts_email: safeTrim(apiLegal.contracts_email) || null,
        legal_phone: safeTrim(apiLegal.legal_phone) || null,
        owner: safeTrim(apiLegal.owner) || null,
        responsible_person: safeTrim(apiLegal.responsible_person) || null,
        responsible_position: safeTrim(apiLegal.responsible_position) || null,
        inn: safeTrim(apiLegal.inn) || null,
        ogrn: safeTrim(apiLegal.ogrn) || null,
        ogrnip: safeTrim(apiLegal.ogrnip) || null,
        okpo: safeTrim(apiLegal.okpo) || null,
        kpp: safeTrim(apiLegal.kpp) || null,
        tax_system: safeTrim(apiLegal.tax_system) || null,
        is_vat_payer: Boolean(apiLegal.is_vat_payer),
        vat_rate_default: Number(apiLegal.vat_rate_default) || 0,
        registration_address: safeTrim(apiLegal.registration_address) || null,
        actual_address: safeTrim(apiLegal.actual_address) || null,
        postal_address: safeTrim(apiLegal.postal_address) || null,
        bank_name: safeTrim(apiLegal.bank_name) || null,
        bik: safeTrim(apiLegal.bik) || null,
        checking_account: safeTrim(apiLegal.checking_account) || null,
        correspondent_account: safeTrim(apiLegal.correspondent_account) || null,
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

    const key = safeTrim(apiDocument.key);
    const title = safeTrim(apiDocument.title);
    if (!key || !title) {
        return null;
    }

    const content = apiDocument.content;
    const normalizedContent =
        typeof content === "string" && content.trim() !== ""
            ? content
            : null;

    return {
        id: apiDocument.id ?? null,
        key,
        title,
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
