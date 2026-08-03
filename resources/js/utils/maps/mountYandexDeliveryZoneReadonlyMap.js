import {
    TOMSK_CENTER,
    geometryToYmapsRing,
    isNearTomskArea,
    ringIsNearTomsk,
} from "./yandexCoords";
import { readYandexMapsApiKey } from "../../config/site";

const SCRIPT_ID = "gangsters-yandex-maps-api";

let warnedPolygonOutsideTomsk = false;

/**
 * @param {string} apiKey
 */
function loadYandexMapsApi(apiKey) {
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
        script.src = `https://api-maps.yandex.ru/2.1/?apikey=${encodeURIComponent(apiKey)}&lang=ru_RU`;
        script.async = true;
        script.onload = () => window.ymaps.ready(resolve);
        script.onerror = () => reject(new Error("Не удалось загрузить Яндекс.Карты"));
        document.head.appendChild(script);
    });
}

function resolveMapCenter(explicitCenter, geometry) {
    const ring = geometry ? geometryToYmapsRing(geometry) : [];

    if (explicitCenter && isNearTomskArea(explicitCenter)) {
        return explicitCenter;
    }

    if (ring.length > 0 && isNearTomskArea(ring[0])) {
        return ring[0];
    }

    return TOMSK_CENTER;
}

/**
 * @param {HTMLElement} container
 * @param {{ apiKey: string, geometry?: object|null, center?: [number, number]|null, zoom?: number }} options
 * @returns {Promise<{ destroy: () => void, refit: () => void, polygonShown: boolean }>}
 */
export async function mountYandexDeliveryZoneReadonlyMap(container, options) {
    const { apiKey, geometry = null, center = null, zoom = 12 } = options;

    await loadYandexMapsApi(apiKey);

    const ring = geometry ? geometryToYmapsRing(geometry) : [];
    const mapCenter = resolveMapCenter(center, geometry);
    const canShowPolygon = ring.length >= 3 && ringIsNearTomsk(ring);

    // API 2.1 has no native dark theme; tiles are darkened via CSS on this class.
    container.classList.add("yandex-map-theme-dark");

    const map = new window.ymaps.Map(
        container,
        {
            center: mapCenter,
            zoom: canShowPolygon ? 12 : zoom,
            controls: ["zoomControl"],
        },
        {
            suppressMapOpenBlock: true,
        },
    );

    const refit = () => {
        map.container.fitToViewport();
    };

    refit();

    if (canShowPolygon) {
        const polygon = new window.ymaps.Polygon(
            [ring],
            {},
            {
                fillColor: "#C6242444",
                strokeColor: "#C62424",
                strokeWidth: 2,
                interactive: false,
            },
        );
        map.geoObjects.add(polygon);
        map.setBounds(polygon.geometry.getBounds(), {
            checkZoomRange: true,
            zoomMargin: 32,
        });
    } else if (ring.length >= 3 && !warnedPolygonOutsideTomsk) {
        warnedPolygonOutsideTomsk = true;
        console.warn(
            "Delivery zone map: polygon is outside Tomsk area or invalid — showing map without zone.",
        );
    }

    return {
        polygonShown: canShowPolygon,
        refit,
        destroy() {
            map.destroy();
            container.classList.remove("yandex-map-theme-dark");
        },
    };
}

/**
 * @param {HTMLElement} container
 * @param {{
 *   apiKey: string,
 *   center?: [number, number]|null,
 *   balloonContent?: string,
 *   zoom?: number,
 * }} options
 * @returns {Promise<{ destroy: () => void, refit: () => void, placemarkShown: boolean }>}
 */
export async function mountYandexKitchenPlacemarkReadonlyMap(container, options) {
    const {
        apiKey,
        center = null,
        balloonContent = "",
        zoom = 16,
    } = options;

    await loadYandexMapsApi(apiKey);

    const mapCenter =
        center && isNearTomskArea(center) ? center : TOMSK_CENTER;
    const canShowPlacemark = center != null && isNearTomskArea(center);

    // API 2.1 has no native dark theme; tiles are darkened via CSS on this class.
    container.classList.add("yandex-map-theme-dark");

    const map = new window.ymaps.Map(
        container,
        {
            center: mapCenter,
            zoom: canShowPlacemark ? zoom : 12,
            controls: ["zoomControl"],
        },
        {
            suppressMapOpenBlock: true,
        },
    );

    const refit = () => {
        map.container.fitToViewport();
    };

    refit();

    if (canShowPlacemark) {
        const placemark = new window.ymaps.Placemark(
            center,
            { balloonContent },
            {
                preset: "islands#redDotIcon",
            },
        );
        map.geoObjects.add(placemark);
    }

    return {
        placemarkShown: canShowPlacemark,
        refit,
        destroy() {
            map.destroy();
            container.classList.remove("yandex-map-theme-dark");
        },
    };
}

/**
 * @returns {string|null}
 */
export function readYandexMapsApiKeyFromSite() {
    return readYandexMapsApiKey();
}
