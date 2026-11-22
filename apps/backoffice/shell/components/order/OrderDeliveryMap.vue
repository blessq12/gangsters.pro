<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from "vue";
import {
    TOMSK_CENTER,
    loadYandexMapsApi,
    parseYandexGeoObject,
    readYandexMapsApiKey,
} from "../../maps/yandexMapsApi.js";

const props = defineProps({
    city: { type: String, default: "" },
    street: { type: String, default: "" },
    house: { type: String, default: "" },
});

const mapHost = ref(null);
const status = ref("loading");
const statusText = ref("Загрузка карты…");

/** @type {any} */
let map = null;
/** @type {any} */
let placemark = null;
let destroyed = false;
let geocodeToken = 0;

const apiKey = readYandexMapsApiKey();

const query = computed(() => {
    const parts = [props.city, props.street, props.house]
        .map((part) => String(part || "").trim())
        .filter(Boolean);
    return parts.join(", ");
});

/**
 * @param {[number, number]} coords
 * @param {boolean} pan
 */
function setPlacemark(coords, pan = true) {
    if (destroyed || !map || !Array.isArray(coords) || coords.length < 2) {
        return;
    }
    try {
        if (!placemark) {
            placemark = new window.ymaps.Placemark(
                coords,
                {},
                {
                    preset: "islands#redDotIcon",
                    draggable: false,
                },
            );
            map.geoObjects.add(placemark);
        } else {
            placemark.geometry.setCoordinates(coords);
        }
        if (pan) {
            map.setCenter(coords, Math.max(map.getZoom(), 16));
        }
    } catch {
        /* ymaps может кинуть при гонке unmount */
    }
}

async function geocodeQuery(text) {
    const token = ++geocodeToken;
    if (!text || destroyed || typeof window.ymaps === "undefined") {
        return;
    }
    try {
        const result = await window.ymaps.geocode(text, { results: 1 });
        if (destroyed || token !== geocodeToken) {
            return;
        }
        const geoObject = result.geoObjects.get(0);
        const parsed = parseYandexGeoObject(geoObject);
        if (!parsed) {
            status.value = "error";
            statusText.value = "Адрес на карте не найден";
            return;
        }
        status.value = "ready";
        statusText.value = "";
        setPlacemark([parsed.latitude, parsed.longitude], true);
    } catch {
        if (destroyed || token !== geocodeToken) {
            return;
        }
        status.value = "error";
        statusText.value = "Не удалось найти адрес на карте";
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
        map = new window.ymaps.Map(
            mapHost.value,
            {
                center: TOMSK_CENTER,
                zoom: 12,
                controls: ["zoomControl"],
            },
            {
                suppressMapOpenBlock: true,
            },
        );
        status.value = "ready";
        statusText.value = "";
        if (query.value) {
            await geocodeQuery(query.value);
        }
        map.container.fitToViewport();
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
    placemark = null;
}

watch(query, (value) => {
    if (status.value === "ready" || status.value === "error") {
        if (value) {
            status.value = "loading";
            statusText.value = "Ищем адрес…";
            geocodeQuery(value);
        }
    }
});

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
    <div class="relative h-56 w-full overflow-hidden border border-bo-border bg-bo-field sm:h-64">
        <div ref="mapHost" class="absolute inset-0" />
        <div
            v-if="status !== 'ready'"
            class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center bg-bo-field/95 px-4 text-sm text-bo-muted"
        >
            {{ statusText }}
        </div>
    </div>
</template>
