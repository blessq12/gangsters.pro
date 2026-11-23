<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from "vue";
import {
    createCityBiasedSuggestProvider,
    geocodeWithCityBias,
    loadYandexMapsApi,
    parseYandexGeoObject,
    readYandexMapsApiKey,
    resolveMapCenter,
} from "../../../../shell/maps/yandexMapsApi.js";

const props = defineProps({
    city: { type: String, default: "" },
    street: { type: String, default: "" },
    house: { type: String, default: "" },
    latitude: { type: [String, Number], default: "" },
    longitude: { type: [String, Number], default: "" },
});

const emit = defineEmits(["update"]);

const mapHost = ref(null);
const searchInput = ref(null);
const status = ref("loading");
const statusText = ref("Загрузка карты…");
const selectedLabel = ref("");

/**
 * Инстансы ymaps нельзя класть в ref()/reactive — Vue Proxy ломает geometry/overlays.
 * @type {any}
 */
let map = null;
/** @type {any} */
let placemark = null;
/** @type {any} */
let suggestView = null;
let destroyed = false;

const apiKey = readYandexMapsApiKey();

const displayAddress = computed(() => {
    if (selectedLabel.value) {
        return selectedLabel.value;
    }
    const parts = [props.city, props.street, props.house].map((part) => String(part || "").trim()).filter(Boolean);
    return parts.length > 0 ? parts.join(", ") : "Адрес не выбран — найди на карте или кликни по точке";
});

const hasCoordinates = computed(() => {
    return toNumberOrNull(props.latitude) !== null && toNumberOrNull(props.longitude) !== null;
});

const coordinateLabel = computed(() => {
    const latitude = toNumberOrNull(props.latitude);
    const longitude = toNumberOrNull(props.longitude);
    if (latitude === null || longitude === null) {
        return "";
    }
    return `${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;
});

function toNumberOrNull(value) {
    if (value === "" || value === null || value === undefined) {
        return null;
    }
    const number = Number(value);
    return Number.isFinite(number) ? number : null;
}

/**
 * @param {{
 *   city: string,
 *   street: string,
 *   house: string,
 *   latitude: number,
 *   longitude: number,
 *   label: string,
 * }} address
 */
function applyAddress(address) {
    selectedLabel.value = address.label || [address.city, address.street, address.house].filter(Boolean).join(", ");
    emit("update", {
        city: address.city,
        street: address.street,
        house: address.house,
        latitude: address.latitude,
        longitude: address.longitude,
    });
}

/**
 * @param {[number, number]} coords
 */
async function reverseGeocode(coords) {
    if (destroyed || !Array.isArray(coords) || coords.length < 2) {
        return;
    }
    try {
        const result = await window.ymaps.geocode(coords, { results: 1 });
        if (destroyed) {
            return;
        }
        const geoObject = result.geoObjects.get(0);
        const parsed = parseYandexGeoObject(geoObject);
        if (!parsed) {
            statusText.value = "Не удалось определить адрес по точке";
            return;
        }
        setPlacemark([parsed.latitude, parsed.longitude], false);
        applyAddress(parsed);
    } catch {
        if (!destroyed) {
            statusText.value = "Не удалось определить адрес по точке";
        }
    }
}

/**
 * @param {[number, number]} coords
 * @param {boolean} [pan]
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
                    draggable: true,
                },
            );
            placemark.events.add("dragend", () => {
                if (!placemark || destroyed) {
                    return;
                }
                const next = placemark.geometry.getCoordinates();
                reverseGeocode(next);
            });
            map.geoObjects.add(placemark);
        } else {
            placemark.geometry.setCoordinates(coords);
        }
        if (pan) {
            map.setCenter(coords, Math.max(map.getZoom(), 16));
        }
    } catch {
        // ymaps может кинуть при гонке unmount
    }
}

/**
 * @param {string} query
 */
async function geocodeQuery(query) {
    const text = String(query || "").trim();
    if (!text || destroyed) {
        return;
    }
    try {
        const result = await geocodeWithCityBias(text, props.city, { results: 1 });
        if (destroyed) {
            return;
        }
        const geoObject = result.geoObjects.get(0);
        const parsed = parseYandexGeoObject(geoObject);
        if (!parsed) {
            statusText.value = "Адрес не найден";
            return;
        }
        setPlacemark([parsed.latitude, parsed.longitude], true);
        applyAddress(parsed);
    } catch {
        if (!destroyed) {
            statusText.value = "Не удалось найти адрес";
        }
    }
}

function waitForHostSize(element, attempts = 30) {
    return new Promise((resolve, reject) => {
        const tick = (left) => {
            if (destroyed) {
                reject(new Error("Компонент размонтирован"));
                return;
            }
            const width = element.offsetWidth;
            const height = element.offsetHeight;
            if (width > 0 && height > 0) {
                resolve();
                return;
            }
            if (left <= 0) {
                reject(new Error("Контейнер карты без размеров"));
                return;
            }
            requestAnimationFrame(() => tick(left - 1));
        };
        tick(attempts);
    });
}

async function mountMap() {
    if (!apiKey) {
        status.value = "error";
        statusText.value = "Нет VITE_YANDEX_MAPS_API_KEY — карта недоступна";
        return;
    }

    try {
        await loadYandexMapsApi(apiKey);
        if (destroyed) {
            return;
        }
        await nextTick();
        const host = mapHost.value;
        const input = searchInput.value;
        if (!host || !input) {
            status.value = "error";
            statusText.value = "Нет контейнера карты";
            return;
        }

        await waitForHostSize(host);
        if (destroyed) {
            return;
        }

        const center = await resolveMapCenter({
            latitude: toNumberOrNull(props.latitude),
            longitude: toNumberOrNull(props.longitude),
            city: props.city,
        });
        if (destroyed) {
            return;
        }
        map = new window.ymaps.Map(
            host,
            {
                center,
                zoom: hasCoordinates.value ? 16 : 12,
                controls: ["zoomControl"],
            },
            {
                suppressMapOpenBlock: true,
            },
        );

        map.events.add("click", (event) => {
            const coords = event.get("coords");
            reverseGeocode(coords);
        });

        suggestView = new window.ymaps.SuggestView(input, {
            results: 7,
            provider: createCityBiasedSuggestProvider(props.city),
        });
        suggestView.events.add("select", (event) => {
            const item = event.get("item");
            const value = item && item.value ? item.value : "";
            geocodeQuery(value);
        });

        if (hasCoordinates.value) {
            setPlacemark(center, false);
            const parts = [props.city, props.street, props.house].filter(Boolean);
            selectedLabel.value = parts.join(", ");
        }

        map.container.fitToViewport();
        status.value = "ready";
        statusText.value = "";
    } catch (err) {
        destroyMap();
        if (!destroyed) {
            status.value = "error";
            statusText.value = err instanceof Error ? err.message : "Ошибка карты";
        }
    }
}

function destroyMap() {
    try {
        if (suggestView && typeof suggestView.destroy === "function") {
            suggestView.destroy();
        }
    } catch {
        // ignore
    }
    suggestView = null;
    placemark = null;
    try {
        if (map && typeof map.destroy === "function") {
            map.destroy();
        }
    } catch {
        // ignore
    }
    map = null;
}

function onSearchSubmit(event) {
    event.preventDefault();
    const value = searchInput.value ? searchInput.value.value : "";
    geocodeQuery(value);
}

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
    <div class="space-y-3">
        <p class="text-sm text-bo-muted">
            Найди адрес поиском или кликни / перетащи метку на карте. Город, улица, дом и координаты подставятся сами.
        </p>
        <form class="flex gap-2" @submit="onSearchSubmit">
            <input
                ref="searchInput"
                type="search"
                class="h-11 min-w-0 flex-1 border border-bo-border bg-bo-field px-3 text-sm text-bo-fg outline-none placeholder:text-bo-muted focus:border-bo-accent"
                placeholder="Поиск адреса"
                autocomplete="off"
                :disabled="status !== 'ready'"
            >
            <button
                type="submit"
                class="h-11 shrink-0 border border-bo-border bg-white/10 px-4 text-sm text-bo-fg transition-colors hover:border-bo-border-strong disabled:opacity-40"
                :disabled="status !== 'ready'"
            >
                Найти
            </button>
        </form>
        <div class="relative h-80 w-full border border-bo-border bg-bo-field">
            <div
                ref="mapHost"
                class="absolute inset-0"
            />
            <div
                v-if="status !== 'ready'"
                class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center bg-bo-field/95 px-4 text-sm text-bo-muted"
            >
                {{ statusText }}
            </div>
        </div>
        <div class="border border-bo-border bg-bo-field px-4 py-3 text-sm text-bo-fg">
            <div class="text-xs text-bo-muted">
                Выбранный адрес
            </div>
            <div class="mt-1">
                {{ displayAddress }}
            </div>
            <div
                v-if="hasCoordinates"
                class="mt-1 text-xs text-bo-muted"
            >
                {{ coordinateLabel }}
            </div>
        </div>
    </div>
</template>
