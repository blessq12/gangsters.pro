import {
    kitchenAddressLabelOrFallback,
} from "../system/companyDeliveryFacts";
import { kitchenMapCenterOrNull } from "./companyDeliveryZoneMap";
import {
    mountYandexKitchenPlacemarkReadonlyMap,
    readYandexMapsApiKeyFromSite,
} from "./mountYandexDeliveryZoneReadonlyMap";

/**
 * @param {HTMLElement} container
 * @param {object|null|undefined} company
 * @returns {Promise<{ destroy: () => void, refit: () => void, placemarkShown: boolean }|null>}
 */
export async function mountCompanyKitchenPlacemarkReadonlyMap(container, company) {
    const apiKey = readYandexMapsApiKeyFromSite();

    if (!apiKey) {
        return null;
    }

    return mountYandexKitchenPlacemarkReadonlyMap(container, {
        apiKey,
        center: kitchenMapCenterOrNull(company),
        balloonContent: kitchenAddressLabelOrFallback(company),
    });
}
