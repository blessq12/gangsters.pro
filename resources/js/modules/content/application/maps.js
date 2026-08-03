import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import { readYandexMapsApiKey } from "../../../config/site";
import {
    buildYandexMapKitchenPointWidgetUrl,
    buildYandexMapWidgetSearchUrl,
    kitchenAddressLabelOrFallback,
} from "./company";

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


/**
 * Режим карты в доке «Оплата и доставка».
 *
 * @typedef {'zone-sdk' | 'widget' | 'loading' | 'fallback'} DeliveryDockMapMode
 */

/**
 * @param {{
 *   hasApiKey: boolean,
 *   hasAddress: boolean,
 *   isLoading: boolean,
 * }} input
 * @returns {DeliveryDockMapMode}
 */
export function resolveDeliveryDockMapMode(input) {
    if (input.hasApiKey) {
        return "zone-sdk";
    }
    if (input.hasAddress) {
        return "widget";
    }
    if (input.isLoading) {
        return "loading";
    }
    return "fallback";
}


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


/** Томск — [широта, долгота] для Яндекс.Карт. Синхронизировать с public/js/maps/yandexGeoJsonCoords.js */
export const TOMSK_CENTER = [56.49771, 84.97437];

const TOMSK_AREA = {
    latMin: 55.0,
    latMax: 58.0,
    lonMin: 82.0,
    lonMax: 87.0,
};

function isFinitePair(a, b) {
    return Number.isFinite(a) && Number.isFinite(b);
}

function looksLikeLatitude(value) {
    return value >= 41 && value <= 82;
}

function looksLikeLongitude(value) {
    return value >= 19 && value <= 180;
}

export function isValidYmapsCenter(coords) {
    if (!coords || coords.length < 2) {
        return false;
    }
    const lat = Number(coords[0]);
    const lng = Number(coords[1]);
    return (
        isFinitePair(lat, lng) &&
        looksLikeLatitude(lat) &&
        looksLikeLongitude(lng)
    );
}

export function isNearTomskArea(coords) {
    if (!isValidYmapsCenter(coords)) {
        return false;
    }
    const lat = Number(coords[0]);
    const lng = Number(coords[1]);
    return (
        lat >= TOMSK_AREA.latMin &&
        lat <= TOMSK_AREA.latMax &&
        lng >= TOMSK_AREA.lonMin &&
        lng <= TOMSK_AREA.lonMax
    );
}

export function geoJsonPositionToYmaps(position) {
    if (!position || position.length < 2) {
        return null;
    }
    const a = Number(position[0]);
    const b = Number(position[1]);
    if (!isFinitePair(a, b)) {
        return null;
    }

    if (looksLikeLongitude(a) && looksLikeLatitude(b)) {
        const coords = [b, a];
        return isValidYmapsCenter(coords) ? coords : null;
    }

    if (looksLikeLatitude(a) && looksLikeLongitude(b)) {
        const coords = [a, b];
        return isValidYmapsCenter(coords) ? coords : null;
    }

    return null;
}

export function pairToYmapsCenter(first, second) {
    if (first == null || second == null) {
        return null;
    }
    const lat = Number(first);
    const lng = Number(second);
    if (!isFinitePair(lat, lng)) {
        return null;
    }

    if (looksLikeLatitude(lat) && looksLikeLongitude(lng)) {
        const coords = [lat, lng];
        return isValidYmapsCenter(coords) ? coords : null;
    }

    if (looksLikeLatitude(lng) && looksLikeLongitude(lat)) {
        const coords = [lng, lat];
        return isValidYmapsCenter(coords) ? coords : null;
    }

    return null;
}

export function geometryToYmapsRing(geometry) {
    if (!geometry?.coordinates) {
        return [];
    }
    let ring = [];
    if (geometry.type === "Polygon") {
        ring = geometry.coordinates[0] || [];
    } else if (geometry.type === "MultiPolygon") {
        ring = geometry.coordinates[0]?.[0] || [];
    }
    return ring
        .map((pos) => geoJsonPositionToYmaps(pos))
        .filter((c) => c !== null);
}

export function ringIsNearTomsk(ring) {
    if (!ring || ring.length < 3) {
        return false;
    }
    return ring.every((coords) => isNearTomskArea(coords));
}


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


/**
 * Readonly-карта зоны доставки (Яндекс SDK / виджет / fallback).
 *
 * @param {{
 *   facts: import('vue').ComputedRef<object|null|undefined>,
 *   deliveryLoading: import('vue').ComputedRef<boolean>,
 *   mountDelayMs?: number,
 * }} options
 */
export function useDeliveryZoneReadonlyMap({
    facts,
    deliveryLoading,
    mountDelayMs = 0,
}) {
    const mapUrl = computed(() => buildYandexMapWidgetSearchUrl(facts.value));

    const hasZoneGeometry = computed(() =>
        hasDeliveryZoneGeometry(facts.value),
    );

    const hasMapsApiKey = computed(() => Boolean(readYandexMapsApiKeyFromSite()));

    const hasKitchenAddress = computed(() => Boolean(mapUrl.value));

    const mapDisplayMode = computed(() =>
        resolveDeliveryDockMapMode({
            hasApiKey: hasMapsApiKey.value,
            hasAddress: hasKitchenAddress.value,
            isLoading: deliveryLoading.value && !facts.value,
        }),
    );

    const mapContainerRef = ref(null);
    /** @type {import('vue').Ref<{ destroy: () => void, refit: () => void, polygonShown?: boolean }|null>} */
    const zoneMapController = ref(null);
    const zoneMapMountFailed = ref(false);
    const zonePolygonShown = ref(false);

    /** @type {import('vue').Ref<ResizeObserver|null>} */
    const mapResizeObserver = ref(null);
    /** @type {ReturnType<typeof setTimeout>|null} */
    let mountDelayTimer = null;

    const showZonePolygonHint = computed(
        () =>
            mapDisplayMode.value === "zone-sdk" &&
            hasZoneGeometry.value &&
            !zonePolygonShown.value &&
            !zoneMapMountFailed.value &&
            zoneMapController.value != null,
    );

    function disconnectResizeObserver() {
        mapResizeObserver.value?.disconnect();
        mapResizeObserver.value = null;
    }

    function clearMountDelayTimer() {
        if (mountDelayTimer != null) {
            clearTimeout(mountDelayTimer);
            mountDelayTimer = null;
        }
    }

    function connectResizeObserver() {
        disconnectResizeObserver();
        if (!mapContainerRef.value || !zoneMapController.value) {
            return;
        }
        mapResizeObserver.value = new ResizeObserver(() => {
            zoneMapController.value?.refit?.();
        });
        mapResizeObserver.value.observe(mapContainerRef.value);
    }

    function destroyZoneMap() {
        disconnectResizeObserver();
        zoneMapController.value?.destroy();
        zoneMapController.value = null;
        zonePolygonShown.value = false;
    }

    async function syncZoneMap() {
        destroyZoneMap();
        zoneMapMountFailed.value = false;

        if (mapDisplayMode.value !== "zone-sdk" || !mapContainerRef.value) {
            return;
        }

        try {
            const controller = await mountCompanyDeliveryZoneReadonlyMap(
                mapContainerRef.value,
                facts.value,
            );
            if (!controller) {
                zoneMapMountFailed.value = true;
                return;
            }
            zoneMapController.value = controller;
            zonePolygonShown.value = controller.polygonShown === true;
            connectResizeObserver();
            controller.refit?.();
        } catch (error) {
            console.error("Delivery zone map:", error);
            zoneMapMountFailed.value = true;
        }
    }

    function scheduleSyncZoneMap() {
        clearMountDelayTimer();
        requestAnimationFrame(() => {
            if (mountDelayMs <= 0) {
                void nextTick().then(() => syncZoneMap());
                return;
            }
            mountDelayTimer = setTimeout(() => {
                mountDelayTimer = null;
                void nextTick().then(() => syncZoneMap());
            }, mountDelayMs);
        });
    }

    watch([mapDisplayMode, facts], async () => {
        await nextTick();
        if (mapDisplayMode.value === "zone-sdk") {
            scheduleSyncZoneMap();
        } else {
            clearMountDelayTimer();
            destroyZoneMap();
            zoneMapMountFailed.value = false;
        }
    });

    onMounted(() => {
        scheduleSyncZoneMap();
    });

    onUnmounted(() => {
        clearMountDelayTimer();
        destroyZoneMap();
        zoneMapMountFailed.value = false;
    });

    return {
        mapUrl,
        mapDisplayMode,
        mapContainerRef,
        zoneMapMountFailed,
        showZonePolygonHint,
    };
}


/**
 * Readonly-карта адреса кухни (Яндекс SDK / виджет / fallback).
 *
 * @param {{
 *   facts: import('vue').ComputedRef<object|null|undefined>,
 *   deliveryLoading: import('vue').ComputedRef<boolean>,
 * }} options
 */
export function useKitchenAddressReadonlyMap({ facts, deliveryLoading }) {
    const mapUrl = computed(() => buildYandexMapKitchenPointWidgetUrl(facts.value));

    const addressLabel = computed(() =>
        kitchenAddressLabelOrFallback(facts.value),
    );

    const hasKitchenPoint = computed(() => Boolean(addressLabel.value));

    const hasMapsApiKey = computed(() => Boolean(readYandexMapsApiKeyFromSite()));

    const mapDisplayMode = computed(() =>
        resolveDeliveryDockMapMode({
            hasApiKey: hasMapsApiKey.value,
            hasAddress: hasKitchenPoint.value,
            isLoading: deliveryLoading.value && !facts.value,
        }),
    );

    const mapContainerRef = ref(null);
    /** @type {import('vue').Ref<{ destroy: () => void, refit: () => void, placemarkShown?: boolean }|null>} */
    const mapController = ref(null);
    const mapMountFailed = ref(false);
    const placemarkShown = ref(false);

    /** @type {import('vue').Ref<ResizeObserver|null>} */
    const mapResizeObserver = ref(null);

    const showPlacemarkHint = computed(
        () =>
            mapDisplayMode.value === "zone-sdk" &&
            !placemarkShown.value &&
            !mapMountFailed.value &&
            mapController.value != null,
    );

    function disconnectResizeObserver() {
        mapResizeObserver.value?.disconnect();
        mapResizeObserver.value = null;
    }

    function connectResizeObserver() {
        disconnectResizeObserver();
        if (!mapContainerRef.value || !mapController.value) {
            return;
        }
        mapResizeObserver.value = new ResizeObserver(() => {
            mapController.value?.refit?.();
        });
        mapResizeObserver.value.observe(mapContainerRef.value);
    }

    function destroyMap() {
        disconnectResizeObserver();
        mapController.value?.destroy();
        mapController.value = null;
        placemarkShown.value = false;
    }

    async function syncMap() {
        destroyMap();
        mapMountFailed.value = false;

        if (mapDisplayMode.value !== "zone-sdk" || !mapContainerRef.value) {
            return;
        }

        try {
            const controller = await mountCompanyKitchenPlacemarkReadonlyMap(
                mapContainerRef.value,
                facts.value,
            );
            if (!controller) {
                mapMountFailed.value = true;
                return;
            }
            mapController.value = controller;
            placemarkShown.value = controller.placemarkShown === true;
            connectResizeObserver();
            controller.refit?.();
        } catch (error) {
            console.error("Kitchen address map:", error);
            mapMountFailed.value = true;
        }
    }

    watch([mapDisplayMode, facts], async () => {
        await nextTick();
        if (mapDisplayMode.value === "zone-sdk") {
            void syncMap();
        } else {
            destroyMap();
            mapMountFailed.value = false;
        }
    });

    onMounted(() => {
        void nextTick().then(() => syncMap());
    });

    onUnmounted(() => {
        destroyMap();
        mapMountFailed.value = false;
    });

    return {
        mapUrl,
        addressLabel,
        mapDisplayMode,
        mapContainerRef,
        mapMountFailed,
        showPlacemarkHint,
    };
}
