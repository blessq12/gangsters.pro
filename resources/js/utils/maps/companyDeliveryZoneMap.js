import {
    mountYandexDeliveryZoneReadonlyMap,
    readYandexMapsApiKeyFromSite,
} from "./mountYandexDeliveryZoneReadonlyMap";
import { pairToYmapsCenter } from "./yandexCoords";

/**
 * @param {object|null|undefined} company
 */
export function hasDeliveryZoneGeometry(company) {
    const geometry = company?.delivery_zone_geojson;
    if (!geometry || typeof geometry !== "object") {
        return false;
    }
    return geometry.type === "Polygon" || geometry.type === "MultiPolygon";
}

/**
 * @param {object|null|undefined} company
 * @returns {[number, number]|null}
 */
export function kitchenMapCenterOrNull(company) {
    return pairToYmapsCenter(
        company?.kitchen_latitude,
        company?.kitchen_longitude,
    );
}

/**
 * @param {HTMLElement} container
 * @param {object|null|undefined} company
 * @returns {Promise<{ destroy: () => void, refit: () => void, polygonShown: boolean }|null>}
 */
export async function mountCompanyDeliveryZoneReadonlyMap(container, company) {
    const apiKey = readYandexMapsApiKeyFromSite();

    if (!apiKey) {
        return null;
    }

    const geometry = hasDeliveryZoneGeometry(company)
        ? company?.delivery_zone_geojson ?? null
        : null;

    return mountYandexDeliveryZoneReadonlyMap(container, {
        apiKey,
        geometry,
        center: kitchenMapCenterOrNull(company),
    });
}
