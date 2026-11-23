<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from "vue";
import {
    TOMSK_CENTER,
    geocodeWithCityBias,
    loadYandexMapsApi,
    parseYandexGeoObject,
    readYandexMapsApiKey,
    resolveMapCenter,
} from "../../maps/yandexMapsApi.js";

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
    defaultCity: { type: String, default: "" },
});

const emit = defineEmits(["toggle"]);

const mapHost = ref(null);
const status = ref("loading");
const statusText = ref("Загрузка карты…");

/** @type {any} */
let map = null;
/** @type {Map<number, any>} */
const placemarks = new Map();
/** @type {Map<number, [number, number]>} */
const coordsById = new Map();
let destroyed = false;
let syncToken = 0;

const apiKey = readYandexMapsApiKey();

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
    return String(order.city || props.defaultCity || "").trim();
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
        if (destroyed) {
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
    if (destroyed || !map) {
        return;
    }
    const id = Number(order.id);
    let mark = placemarks.get(id);
    const balloon = [
        order.title,
        order.subtitle,
        [order.street, order.house].filter(Boolean).join(", "),
    ].filter(Boolean).join("\n");

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

function clearMissing(orderIds) {
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
    if (destroyed || !map || coordsById.size === 0) {
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
    if (!map || destroyed) {
        return;
    }

    /** @type {RouteMapOrder[]} */
    const orders = Array.isArray(props.orders) ? props.orders : [];
    clearMissing(orders.map((order) => Number(order.id)));

    for (const order of orders) {
        if (destroyed || token !== syncToken) {
            return;
        }
        const coords = await resolveCoords(order);
        if (destroyed || token !== syncToken) {
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
    if (!destroyed && token === syncToken) {
        status.value = "ready";
        statusText.value = "";
    }
}

async function mountMap() {
    if (!mapHost.value) {
        return;
    }
    if (!apiKey) {
        status.value = "error";
        statusText.value = "Нет ключа Яндекс.Карт";
        return;
    }
    try {
        status.value = "loading";
        statusText.value = "Загрузка карты…";
        await loadYandexMapsApi(apiKey);
        await nextTick();
        if (destroyed || !mapHost.value) {
            return;
        }
        const center = await resolveMapCenter({ city: props.defaultCity });
        if (destroyed || !mapHost.value) {
            return;
        }
        map = new window.ymaps.Map(
            mapHost.value,
            {
                center: center || TOMSK_CENTER,
                zoom: 12,
                controls: ["zoomControl"],
            },
            {
                suppressMapOpenBlock: true,
            },
        );
        map.container.fitToViewport();
        statusText.value = "Расставляем точки…";
        await syncMarkers();
    } catch {
        if (!destroyed) {
            status.value = "error";
            statusText.value = "Не удалось загрузить карту";
        }
    }
}

function destroyMap() {
    try {
        if (map && typeof map.destroy === "function") {
            map.destroy();
        }
    } catch {
        /* noop */
    }
    map = null;
    placemarks.clear();
    coordsById.clear();
}

watch(
    () => props.orders,
    () => {
        if (map && !destroyed) {
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

function refreshViewport() {
    if (destroyed || !map) {
        return;
    }
    try {
        map.container.fitToViewport();
        fitBounds();
    } catch {
        /* noop */
    }
}

defineExpose({ refreshViewport });

onMounted(() => {
    destroyed = false;
    mountMap();
});

onBeforeUnmount(() => {
    destroyed = true;
    destroyMap();
});
</script>

<template>
    <div class="relative h-full min-h-0 w-full overflow-hidden border border-bo-border bg-bo-field">
        <div
            ref="mapHost"
            class="absolute inset-0"
        />
        <div
            v-if="status !== 'ready'"
            class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center bg-bo-field/90 px-4 text-sm text-bo-muted"
        >
            {{ statusText }}
        </div>
    </div>
</template>
