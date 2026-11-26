<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import {
    createCityBiasedSuggestProvider,
    geocodeWithCityBias,
    parseYandexGeoObject,
} from "../../maps/yandexMapsApi.js";
import { useYandexMap } from "../../composables/useYandexMap.js";

const props = defineProps({
    /** Город филиала — bias для suggest/geocode. */
    biasCity: { type: String, default: "" },
    city: { type: String, default: "" },
    street: { type: String, default: "" },
    house: { type: String, default: "" },
    latitude: { type: [String, Number], default: "" },
    longitude: { type: [String, Number], default: "" },
    disabled: { type: Boolean, default: false },
    /** Подсказка над поиском */
    hint: {
        type: String,
        default:
            "Найди адрес поиском или кликни / перетащи метку на карте. Улица, дом и координаты подставятся сами.",
    },
});

const emit = defineEmits(["update"]);

const searchInput = ref(null);
const selectedLabel = ref("");

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
} = useYandexMap();

/** @type {any} */
let placemark = null;
/** @type {any} */
let suggestView = null;

const effectiveBiasCity = computed(() => String(props.biasCity || props.city || "").trim());

const displayAddress = computed(() => {
    if (selectedLabel.value) {
        return selectedLabel.value;
    }
    const parts = [props.city, props.street, props.house]
        .map((part) => String(part || "").trim())
        .filter(Boolean);
    return parts.length > 0
        ? parts.join(", ")
        : "Адрес не выбран — найди на карте или кликни по точке";
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
    selectedLabel.value =
        address.label || [address.city, address.street, address.house].filter(Boolean).join(", ");
    emit("update", {
        city: address.city,
        street: address.street,
        house: address.house,
        latitude: address.latitude,
        longitude: address.longitude,
        label: address.label || "",
    });
}

/**
 * @param {[number, number]} coords
 */
async function reverseGeocode(coords) {
    if (isDestroyed() || !Array.isArray(coords) || coords.length < 2) {
        return;
    }
    try {
        const result = await window.ymaps.geocode(coords, { results: 1 });
        if (isDestroyed()) {
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
        if (!isDestroyed()) {
            statusText.value = "Не удалось определить адрес по точке";
        }
    }
}

/**
 * @param {[number, number]} coords
 * @param {boolean} [pan]
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
                    draggable: !props.disabled,
                },
            );
            placemark.events.add("dragend", () => {
                if (!placemark || isDestroyed()) {
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
        /* ymaps / unmount */
    }
}

/**
 * @param {string} query
 */
async function geocodeQuery(query) {
    const text = String(query || "").trim();
    if (!text || isDestroyed()) {
        return;
    }
    try {
        const result = await geocodeWithCityBias(text, effectiveBiasCity.value, { results: 1 });
        if (isDestroyed()) {
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
        if (!isDestroyed()) {
            statusText.value = "Не удалось найти адрес";
        }
    }
}

function destroySuggest() {
    try {
        if (suggestView && typeof suggestView.destroy === "function") {
            suggestView.destroy();
        }
    } catch {
        /* ignore */
    }
    suggestView = null;
}

async function initPicker() {
    const map = await mountMap({
        latitude: toNumberOrNull(props.latitude),
        longitude: toNumberOrNull(props.longitude),
        city: effectiveBiasCity.value,
        zoom: hasCoordinates.value ? 16 : 12,
        waitForSize: true,
    });
    if (isDestroyed() || !map) {
        return;
    }

    const input = searchInput.value;
    if (!input) {
        status.value = "error";
        statusText.value = "Нет контейнера карты";
        return;
    }

    map.events.add("click", (event) => {
        if (props.disabled) {
            return;
        }
        const coords = event.get("coords");
        reverseGeocode(coords);
    });

    destroySuggest();
    suggestView = new window.ymaps.SuggestView(input, {
        results: 7,
        provider: createCityBiasedSuggestProvider(effectiveBiasCity.value),
    });
    suggestView.events.add("select", (event) => {
        const item = event.get("item");
        const value = item && item.value ? item.value : "";
        geocodeQuery(value);
    });

    if (hasCoordinates.value) {
        const lat = toNumberOrNull(props.latitude);
        const lng = toNumberOrNull(props.longitude);
        if (lat !== null && lng !== null) {
            setPlacemark([lat, lng], false);
        }
        const parts = [props.city, props.street, props.house].filter(Boolean);
        selectedLabel.value = parts.join(", ");
    }

    fitToViewport();
}

function onSearchSubmit(event) {
    event.preventDefault();
    if (props.disabled) {
        return;
    }
    const value = searchInput.value ? searchInput.value.value : "";
    geocodeQuery(value);
}

onMounted(() => {
    resetDestroyed();
    initPicker();
});

onBeforeUnmount(() => {
    markDestroyed();
    destroySuggest();
    placemark = null;
    destroyMap();
});
</script>

<template>
    <div class="space-y-3">
        <p class="text-sm text-bo-muted">
            {{ hint }}
        </p>
        <form class="flex gap-2" @submit="onSearchSubmit">
            <input
                ref="searchInput"
                type="search"
                class="h-11 min-w-0 flex-1 border border-bo-border bg-bo-field px-3 text-sm text-bo-fg outline-none placeholder:text-bo-muted focus:border-bo-accent"
                placeholder="Поиск адреса"
                autocomplete="off"
                :disabled="disabled || status !== 'ready'"
            />
            <button
                type="submit"
                class="h-11 shrink-0 border border-bo-border bg-white/10 px-4 text-sm text-bo-fg transition-colors hover:border-bo-border-strong disabled:opacity-40"
                :disabled="disabled || status !== 'ready'"
            >
                Найти
            </button>
        </form>
        <div class="relative h-80 w-full border border-bo-border bg-bo-field">
            <div ref="mapHost" class="absolute inset-0" />
            <div
                v-if="status !== 'ready'"
                class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center bg-bo-field/95 px-4 text-sm text-bo-muted"
            >
                {{ statusText }}
            </div>
        </div>
        <div class="border border-bo-border bg-bo-field px-4 py-3 text-sm text-bo-fg">
            <div class="text-xs text-bo-muted">Выбранный адрес</div>
            <div class="mt-1">
                {{ displayAddress }}
            </div>
            <div v-if="hasCoordinates" class="mt-1 text-xs text-bo-muted">
                {{ coordinateLabel }}
            </div>
        </div>
    </div>
</template>
