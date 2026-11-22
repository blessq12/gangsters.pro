const SCRIPT_ID = "gangsters-backoffice-yandex-maps-api";

/** Томск — дефолтный центр карты. */
export const TOMSK_CENTER = [56.49771, 84.97437];

/**
 * @returns {string|null}
 */
export function readYandexMapsApiKey() {
    const key = import.meta.env?.VITE_YANDEX_MAPS_API_KEY;
    return typeof key === "string" && key.trim() !== "" ? key.trim() : null;
}

/**
 * @param {string} apiKey
 * @returns {Promise<void>}
 */
export function loadYandexMapsApi(apiKey) {
    if (typeof window.ymaps !== "undefined") {
        return new Promise((resolve) => {
            window.ymaps.ready(resolve);
        });
    }

    const existing = document.getElementById(SCRIPT_ID);
    if (existing) {
        return new Promise((resolve, reject) => {
            existing.addEventListener("load", () => window.ymaps.ready(resolve), { once: true });
            existing.addEventListener("error", reject, { once: true });
        });
    }

    return new Promise((resolve, reject) => {
        const script = document.createElement("script");
        script.id = SCRIPT_ID;
        // package.full — нужен editor полигонов (зоны доставки).
        script.src = `https://api-maps.yandex.ru/2.1/?apikey=${encodeURIComponent(apiKey)}&lang=ru_RU&load=package.full`;
        script.async = true;
        script.onload = () => window.ymaps.ready(resolve);
        script.onerror = () => reject(new Error("Не удалось загрузить Яндекс.Карты"));
        document.head.appendChild(script);
    });
}

/**
 * @param {unknown} geoObject
 * @returns {{
 *   city: string,
 *   street: string,
 *   house: string,
 *   latitude: number,
 *   longitude: number,
 *   label: string,
 * }|null}
 */
export function parseYandexGeoObject(geoObject) {
    if (!geoObject?.geometry?.getCoordinates) {
        return null;
    }

    const coords = geoObject.geometry.getCoordinates();
    const latitude = Number(coords?.[0]);
    const longitude = Number(coords?.[1]);
    if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
        return null;
    }

    const meta = geoObject.properties?.get?.("metaDataProperty.GeocoderMetaData")
        ?? geoObject.properties?.get?.("metaDataProperty")?.GeocoderMetaData
        ?? null;
    const components = Array.isArray(meta?.Address?.Components)
        ? meta.Address.Components
        : [];

    /** @type {Record<string, string>} */
    const byKind = {};
    for (const component of components) {
        const kind = String(component?.kind ?? "");
        const name = String(component?.name ?? "").trim();
        if (kind && name && !byKind[kind]) {
            byKind[kind] = name;
        }
    }

    const label = String(
        meta?.text
        ?? geoObject.getAddressLine?.()
        ?? geoObject.properties?.get?.("text")
        ?? "",
    ).trim();

    return {
        city: byKind.locality || byKind.area || byKind.province || "",
        street: byKind.street || byKind.thoroughfare || "",
        house: byKind.house || "",
        latitude,
        longitude,
        label,
    };
}
