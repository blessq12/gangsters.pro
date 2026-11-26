/**
 * Единый контракт адреса доставки (заказ / клиент / checkout).
 * Согласован с бэкенд-формами: city + street/house + lat/lng.
 */

/**
 * @typedef {{
 *   city: string,
 *   street: string,
 *   house: string,
 *   entrance?: string,
 *   floor?: string,
 *   apartment?: string,
 *   comment?: string,
 *   latitude: number|null,
 *   longitude: number|null,
 * }} DeliveryAddress
 */

/**
 * @typedef {{
 *   value: string,
 *   city: string,
 *   street: string,
 *   house: string,
 *   entrance: string,
 *   floor: string,
 *   apartment: string,
 *   lat: number|null,
 *   lng: number|null,
 * }} DaDataAddressHit
 */

/**
 * @param {unknown} value
 * @returns {number|null}
 */
export function toCoordOrNull(value) {
    if (value === "" || value === null || value === undefined) {
        return null;
    }
    const number = Number(value);
    return Number.isFinite(number) ? number : null;
}

/**
 * Hit ДаДата → DeliveryAddress. Город филиала важнее city из hit.
 *
 * @param {DaDataAddressHit|Record<string, unknown>} hit
 * @param {string} branchCity
 * @returns {DeliveryAddress}
 */
export function deliveryAddressFromDaDataHit(hit, branchCity) {
    const city = String(branchCity || hit?.city || "").trim();
    return {
        city,
        street: String(hit?.street ?? "").trim(),
        house: String(hit?.house ?? "").trim(),
        entrance: String(hit?.entrance ?? "").trim(),
        floor: String(hit?.floor ?? "").trim(),
        apartment: String(hit?.apartment ?? "").trim(),
        comment: "",
        latitude: toCoordOrNull(hit?.lat ?? hit?.latitude),
        longitude: toCoordOrNull(hit?.lng ?? hit?.longitude),
    };
}

/**
 * Подпись для строки поиска / списка.
 *
 * @param {{ city?: string, street?: string, house?: string, value?: string }} parts
 * @returns {string}
 */
export function formatAddressSearchLabel(parts) {
    if (parts?.value) {
        const value = String(parts.value).trim();
        if (value) {
            return value;
        }
    }
    return [parts?.city, parts?.street, parts?.house]
        .map((part) => String(part || "").trim())
        .filter(Boolean)
        .join(", ");
}
