<script setup>
import { computed, onBeforeUnmount, onMounted, watch } from "vue";
import { geocodeWithCityBias, parseYandexGeoObject } from "../../maps/yandexMapsApi.js";
import { useYandexMap } from "../../composables/useYandexMap.js";

const props = defineProps({
    city: { type: String, default: "" },
    street: { type: String, default: "" },
    house: { type: String, default: "" },
    /** Город филиала для bias; по умолчанию — city адреса. */
    biasCity: { type: String, default: "" },
});

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

/** @type {any} */
let placemark = null;
let geocodeToken = 0;

const effectiveBiasCity = computed(() => String(props.biasCity || props.city || "").trim());

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
    const map = getMap();
    if (isDestroyed() || !map || !Array.isArray(coords) || coords.length < 2) {
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
        /* ymaps / unmount */
    }
}

/**
 * @param {string} text
 */
async function geocodeQuery(text) {
    const token = ++geocodeToken;
    if (!text || isDestroyed() || typeof window.ymaps === "undefined") {
        return;
    }
    try {
        const result = await geocodeWithCityBias(text, effectiveBiasCity.value, { results: 1 });
        if (isDestroyed() || token !== geocodeToken) {
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
        if (isDestroyed() || token !== geocodeToken) {
            return;
        }
        status.value = "error";
        statusText.value = "Не удалось найти адрес на карте";
    }
}

async function initMap() {
    status.value = "loading";
    statusText.value = "Загрузка карты…";
    const map = await mountMap({
        city: effectiveBiasCity.value,
        zoom: 12,
        waitForSize: false,
    });
    if (isDestroyed() || !map) {
        return;
    }
    if (query.value) {
        await geocodeQuery(query.value);
    }
    fitToViewport();
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
    resetDestroyed();
    initMap();
});

onBeforeUnmount(() => {
    markDestroyed();
    placemark = null;
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
