function safeTrim(value) {
    if (value == null) return "";
    if (typeof value === "string") return value.trim();
    return String(value).trim();
}

function nullableString(value) {
    const trimmed = safeTrim(value);
    return trimmed !== "" ? trimmed : null;
}

function nullableNumber(value) {
    if (value == null || value === "") return null;
    const n = Number(value);
    return Number.isFinite(n) ? n : null;
}

/**
 * @param {unknown} raw
 * @returns {object|null}
 */
function normalizeKitchenAddress(raw) {
    if (!raw || typeof raw !== "object") {
        return {
            city: null,
            street: null,
            house: null,
            comment: null,
            search_line: null,
        };
    }

    return {
        city: nullableString(raw.city),
        street: nullableString(raw.street),
        house: nullableString(raw.house),
        comment: nullableString(raw.comment),
        search_line: nullableString(raw.search_line),
    };
}

/**
 * @param {unknown} apiData
 * @returns {object|null}
 */
export function normalizeDeliveryData(apiData) {
    if (!apiData || typeof apiData !== "object") {
        return null;
    }

    const rawSettings =
        apiData.settings && typeof apiData.settings === "object"
            ? apiData.settings
            : {};
    const rawZone =
        apiData.zone && typeof apiData.zone === "object" ? apiData.zone : {};

    return {
        settings: {
            min_order_amount_kopecks: nullableNumber(
                rawSettings.min_order_amount_kopecks,
            ),
            delivery_fee_kopecks: nullableNumber(
                rawSettings.delivery_fee_kopecks,
            ),
            outside_zone_delivery_fee_kopecks: nullableNumber(
                rawSettings.outside_zone_delivery_fee_kopecks,
            ),
            average_delivery_time_minutes: nullableNumber(
                rawSettings.average_delivery_time_minutes,
            ),
        },
        zone: {
            kitchen_address: normalizeKitchenAddress(rawZone.kitchen_address),
            kitchen_latitude: nullableNumber(rawZone.kitchen_latitude),
            kitchen_longitude: nullableNumber(rawZone.kitchen_longitude),
            delivery_zone_geojson:
                rawZone.delivery_zone_geojson &&
                typeof rawZone.delivery_zone_geojson === "object"
                    ? rawZone.delivery_zone_geojson
                    : null,
        },
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

    const coverage =
        nullableString(kitchen.search_line) || nullableString(kitchen.city);

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
        city_coverage: coverage,
        kitchen_latitude: zone.kitchen_latitude ?? null,
        kitchen_longitude: zone.kitchen_longitude ?? null,
        delivery_zone_geojson: zone.delivery_zone_geojson ?? null,
    };
}
