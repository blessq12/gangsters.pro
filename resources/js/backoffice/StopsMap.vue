<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from "vue";
import { readYandexMapsApiKey } from "../public/config/site";
import { KITCHEN } from "./opsBoard";

const props = defineProps({
    points: {
        type: Array,
        default: () => [],
    },
});

const container = ref(null);
const error = ref("");
const hasKey = Boolean(readYandexMapsApiKey());

/** @type {{ destroy: () => void } | null} */
let mapInstance = null;

const SCRIPT_ID = "gangsters-yandex-maps-api";

function loadMaps(apiKey) {
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

function destroyMap() {
    if (mapInstance) {
        mapInstance.destroy();
        mapInstance = null;
    }
}

async function draw() {
    destroyMap();
    error.value = "";
    const apiKey = readYandexMapsApiKey();
    if (!apiKey || !container.value) {
        return;
    }

    const line = [
        [KITCHEN.lat, KITCHEN.lng],
        ...props.points
            .filter((point) => typeof point.lat === "number" && typeof point.lng === "number")
            .map((point) => [point.lat, point.lng]),
    ];

    try {
        await loadMaps(apiKey);
        if (!container.value) {
            return;
        }
        const map = new window.ymaps.Map(container.value, {
            center: line[0],
            zoom: 12,
            controls: ["zoomControl"],
        });
        map.geoObjects.add(new window.ymaps.Placemark(line[0], { iconCaption: KITCHEN.label }));
        props.points.forEach((point, index) => {
            if (typeof point.lat !== "number" || typeof point.lng !== "number") {
                return;
            }
            map.geoObjects.add(new window.ymaps.Placemark(
                [point.lat, point.lng],
                { iconCaption: `${index + 1}. ${point.label ?? ""}` },
            ));
        });
        if (line.length > 1) {
            map.geoObjects.add(new window.ymaps.Polyline(line, {}, {
                strokeColor: "#b45309",
                strokeWidth: 4,
            }));
            const bounds = map.geoObjects.getBounds();
            if (bounds) {
                map.setBounds(bounds, { checkZoomRange: true, zoomMargin: 24 });
            }
        }
        mapInstance = map;
    } catch (caught) {
        error.value = caught instanceof Error ? caught.message : "Карта недоступна";
    }
}

watch(
    () => props.points.map((point) => `${point.lat},${point.lng},${point.label}`).join("|"),
    () => {
        draw();
    },
);

onMounted(() => {
    draw();
});

onBeforeUnmount(() => {
    destroyMap();
});
</script>

<template>
    <div>
        <p
            v-if="!hasKey"
            class="text-sm text-neutral-500"
        >
            Ключ Яндекс.Карт не задан. Маршрут остаётся списком остановок.
        </p>
        <p
            v-else-if="error"
            class="text-sm text-red-700"
        >
            {{ error }}
        </p>
        <div
            v-show="hasKey && !error"
            ref="container"
            class="h-72 w-full overflow-hidden rounded border border-neutral-200 bg-neutral-100"
        />
    </div>
</template>
