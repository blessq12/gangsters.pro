const ENDPOINT = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address";

/**
 * @returns {string}
 */
function readDaDataApiKey() {
    const key = import.meta.env?.VITE_DADATA_API_KEY;
    return typeof key === "string" ? key.trim() : "";
}

/**
 * @param {unknown} value
 * @returns {number|null}
 */
function toFloatOrNull(value) {
    if (value === null || value === undefined || value === "") {
        return null;
    }
    const number = Number(value);
    return Number.isFinite(number) ? number : null;
}

/**
 * @param {unknown} value
 * @returns {string}
 */
function asText(value) {
    return String(value ?? "").trim();
}

/**
 * @param {Record<string, unknown>} item
 * @returns {{
 *   value: string,
 *   city: string,
 *   street: string,
 *   house: string,
 *   entrance: string,
 *   floor: string,
 *   apartment: string,
 *   lat: number|null,
 *   lng: number|null,
 * }|null}
 */
function mapSuggestion(item) {
    const value = asText(item.value ?? item.unrestricted_value);
    const data =
        item.data && typeof item.data === "object"
            ? /** @type {Record<string, unknown>} */ (item.data)
            : {};

    const city = asText(data.city ?? data.settlement);
    let street = asText(data.street_with_type ?? data.street);
    if (!street) {
        street = asText(data.settlement_with_type);
    }
    let house = asText(data.house);
    const block = asText(data.block);
    if (house && block) {
        house = `${house}/${block}`;
    }

    const entrance = asText(data.entrance);
    const floor = asText(data.floor);
    const apartment = asText(data.flat);

    if (!value && !street && !house) {
        return null;
    }

    return {
        value: value || [city, street, house].filter(Boolean).join(", "),
        city,
        street,
        house,
        entrance,
        floor,
        apartment,
        lat: toFloatOrNull(data.geo_lat),
        lng: toFloatOrNull(data.geo_lon),
    };
}

/**
 * Подсказки адресов ДаДата с фронта (без бэкенд-прокси).
 *
 * @param {{ query: string, city?: string|null, limit?: number }} params
 * @returns {Promise<Array<{
 *   value: string,
 *   city: string,
 *   street: string,
 *   house: string,
 *   entrance: string,
 *   floor: string,
 *   apartment: string,
 *   lat: number|null,
 *   lng: number|null,
 * }>>}
 */
export async function suggestAddressFromDaData(params) {
    const query = String(params.query ?? "").trim();
    if (query.length < 2) {
        return [];
    }

    const apiKey = readDaDataApiKey();
    if (!apiKey) {
        throw new Error("Нет VITE_DADATA_API_KEY — подсказки недоступны");
    }

    const limit = Math.max(1, Math.min(20, Number(params.limit) || 8));
    const city = String(params.city ?? "").trim();

    /** @type {Record<string, unknown>} */
    const body = {
        query,
        count: limit,
        language: "ru",
    };
    if (city) {
        body.locations = [{ city }];
        body.restrict_value = true;
    }

    const response = await fetch(ENDPOINT, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: `Token ${apiKey}`,
        },
        body: JSON.stringify(body),
    });

    if (!response.ok) {
        throw new Error(`ДаДата недоступна (${response.status})`);
    }

    const payload = await response.json();
    const raw = Array.isArray(payload?.suggestions) ? payload.suggestions : [];
    /** @type {Array<{
     *   value: string,
     *   city: string,
     *   street: string,
     *   house: string,
     *   entrance: string,
     *   floor: string,
     *   apartment: string,
     *   lat: number|null,
     *   lng: number|null,
     * }>} */
    const mapped = [];
    for (const item of raw) {
        if (!item || typeof item !== "object") {
            continue;
        }
        const row = mapSuggestion(/** @type {Record<string, unknown>} */ (item));
        if (row) {
            mapped.push(row);
        }
    }
    return mapped;
}
