<script setup>
import { onBeforeUnmount, onMounted, watch } from "vue";
import { geocodeWithCityBias, parseYandexGeoObject } from "../../maps/yandexMapsApi.js";
import { useYandexMap } from "../../composables/useYandexMap.js";

/**
 * @typedef {{
 *   id: number,
 *   title: string,
 *   subtitle?: string,
 *   street?: string,
 *   house?: string,
 *   city?: string,
 *   latitude?: number|null,
 *   longitude?: number|null,
 * }} RouteMapOrder
 */

const props = defineProps({
    /** @type {import('vue').PropType<RouteMapOrder[]>} */
    orders: { type: Array, default: () => [] },
    /** @type {import('vue').PropType<number[]>} */
    selectedIds: { type: Array, default: () => [] },
    /** Город филиала — bias геокода. */
    biasCity: { type: String, default: "" },
});

const emit = defineEmits(["toggle"]);

const {
    mapHost,
    status,
    statusText,
    mountMap,
    destroyMap,
    getMap,
    isDestroyed,
    resetDestroyed,
    markDestroyed,
    fitToViewport,
} = useYandexMap({
    missingKeyMessage: "Нет ключа Яндекс.Карт",
});

/** @type {Map<number, any>} */
const placemarks = new Map();
/** @type {Map<number, [number, number]>} */
const coordsById = new Map();
let syncToken = 0;

/**
 * @param {unknown} value
 * @returns {number|null}
 */
function toNumberOrNull(value) {
    if (value === "" || value === null || value === undefined) {
        return null;
    }
    const number = Number(value);
    return Number.isFinite(number) ? number : null;
}

/**
 * @param {RouteMapOrder} order
 * @returns {string}
 */
function biasCityFor(order) {
    return String(order.city || props.biasCity || "").trim();
}

/**
 * @param {RouteMapOrder} order
 * @returns {string}
 */
function geocodeQuery(order) {
    return [order.street, order.house]
        .map((part) => String(part || "").trim())
        .filter(Boolean)
        .join(", ");
}

/**
 * @param {RouteMapOrder} order
 * @returns {Promise<[number, number]|null>}
 */
async function resolveCoords(order) {
    const lat = toNumberOrNull(order.latitude);
    const lng = toNumberOrNull(order.longitude);
    if (lat !== null && lng !== null) {
        return [lat, lng];
    }

    const query = geocodeQuery(order);
    if (!query || typeof window.ymaps === "undefined") {
        return null;
    }

    try {
        const result = await geocodeWithCityBias(query, biasCityFor(order), { results: 1 });
        if (isDestroyed()) {
            return null;
        }
        const geoObject = result.geoObjects.get(0);
        const parsed = parseYandexGeoObject(geoObject);
        if (!parsed) {
            return null;
        }
        return [parsed.latitude, parsed.longitude];
    } catch {
        return null;
    }
}

/**
 * @param {number} orderId
 * @returns {boolean}
 */
function isSelected(orderId) {
    return props.selectedIds.map(Number).includes(Number(orderId));
}

/**
 * @param {number} orderId
 * @returns {string}
 */
function presetFor(orderId) {
    return isSelected(orderId) ? "islands#blueIcon" : "islands#grayIcon";
}

/**
 * @param {RouteMapOrder} order
 * @param {[number, number]} coords
 */
function upsertPlacemark(order, coords) {
    const map = getMap();
    if (isDestroyed() || !map) {
        return;
    }
    const id = Number(order.id);
    let mark = placemarks.get(id);
    const balloon = [
        order.title,
        order.subtitle,
        [order.street, order.house].filter(Boolean).join(", "),
    ]
        .filter(Boolean)
        .join("\n");

    try {
        if (!mark) {
            mark = new window.ymaps.Placemark(
                coords,
                {
                    balloonContentHeader: order.title,
                    balloonContentBody: balloon,
                    hintContent: order.title,
                },
                {
                    preset: presetFor(id),
                    draggable: false,
                },
            );
            mark.events.add("click", () => {
                emit("toggle", id);
            });
            map.geoObjects.add(mark);
            placemarks.set(id, mark);
        } else {
            mark.geometry.setCoordinates(coords);
            mark.options.set("preset", presetFor(id));
            mark.properties.set({
                balloonContentHeader: order.title,
                balloonContentBody: balloon,
                hintContent: order.title,
            });
        }
        coordsById.set(id, coords);
    } catch {
        /* ymaps / unmount */
    }
}

/**
 * @param {number[]} orderIds
 */
function clearMissing(orderIds) {
    const map = getMap();
    const keep = new Set(orderIds.map(Number));
    for (const [id, mark] of placemarks.entries()) {
        if (keep.has(id)) {
            continue;
        }
        try {
            map?.geoObjects.remove(mark);
        } catch {
            /* noop */
        }
        placemarks.delete(id);
        coordsById.delete(id);
    }
}

function fitBounds() {
    const map = getMap();
    if (isDestroyed() || !map || coordsById.size === 0) {
        return;
    }
    try {
        if (coordsById.size === 1) {
            const only = [...coordsById.values()][0];
            map.setCenter(only, Math.max(map.getZoom(), 14));
            return;
        }
        const bounds = window.ymaps.util.bounds.fromPoints([...coordsById.values()]);
        map.setBounds(bounds, { checkZoomRange: true, zoomMargin: 48 });
    } catch {
        /* noop */
    }
}

async function syncMarkers() {
    const token = ++syncToken;
    if (!getMap() || isDestroyed()) {
        return;
    }

    /** @type {RouteMapOrder[]} */
    const orders = Array.isArray(props.orders) ? props.orders : [];
    clearMissing(orders.map((order) => Number(order.id)));

    for (const order of orders) {
        if (isDestroyed() || token !== syncToken) {
            return;
        }
        const coords = await resolveCoords(order);
        if (isDestroyed() || token !== syncToken) {
            return;
        }
        if (!coords) {
            continue;
        }
        upsertPlacemark(order, coords);
    }

    for (const [id, mark] of placemarks.entries()) {
        try {
            mark.options.set("preset", presetFor(id));
        } catch {
            /* noop */
        }
    }

    fitBounds();
    if (!isDestroyed() && token === syncToken) {
        status.value = "ready";
        statusText.value = "";
    }
}

async function initMap() {
    status.value = "loading";
    statusText.value = "Загрузка карты…";
    const map = await mountMap({
        city: props.biasCity,
        zoom: 12,
        waitForSize: false,
    });
    if (isDestroyed() || !map) {
        return;
    }
    status.value = "loading";
    statusText.value = "Расставляем точки…";
    await syncMarkers();
}

function refreshViewport() {
    if (isDestroyed() || !getMap()) {
        return;
    }
    fitToViewport();
    fitBounds();
}

defineExpose({ refreshViewport });

watch(
    () => props.orders,
    () => {
        if (getMap() && !isDestroyed()) {
            status.value = "loading";
            statusText.value = "Обновляем точки…";
            syncMarkers();
        }
    },
    { deep: true },
);

watch(
    () => [...props.selectedIds],
    () => {
        for (const [id, mark] of placemarks.entries()) {
            try {
                mark.options.set("preset", presetFor(id));
            } catch {
                /* noop */
            }
        }
    },
);

onMounted(() => {
    resetDestroyed();
    initMap();
});

onBeforeUnmount(() => {
    markDestroyed();
    placemarks.clear();
    coordsById.clear();
    destroyMap();
});
</script>

<template>
    <div class="relative h-full min-h-0 w-full overflow-hidden border border-bo-border bg-bo-field">
        <div ref="mapHost" class="absolute inset-0" />
        <div
            v-if="status !== 'ready'"
            class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center bg-bo-field/90 px-4 text-sm text-bo-muted"
        >
            {{ statusText }}
        </div>
    </div>
</template>
