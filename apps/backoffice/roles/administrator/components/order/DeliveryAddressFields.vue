<script setup>
import { computed, onBeforeUnmount, ref, watch } from "vue";
import { suggestAddressFromDaData } from "../../../../shell/geo/dadataSuggest.js";
import { useToast } from "../../../../shell/composables/useToast.js";
import UiButton from "../../../../shell/components/ui/UiButton.vue";
import UiFormField from "../../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../../shell/components/ui/UiInput.vue";

const props = defineProps({
    city: { type: String, default: "" },
    street: { type: String, default: "" },
    house: { type: String, default: "" },
    entrance: { type: String, default: "" },
    floor: { type: String, default: "" },
    apartment: { type: String, default: "" },
    comment: { type: String, default: "" },
    latitude: { type: [String, Number], default: "" },
    longitude: { type: [String, Number], default: "" },
    /** Город филиала — bias для ДаДата. */
    branchCity: { type: String, default: "" },
    streetError: { type: String, default: "" },
    houseError: { type: String, default: "" },
    /** Статус зоны из quote после выбора адреса. */
    zoneStatus: { type: String, default: "" },
    mapOpen: { type: Boolean, default: false },
});

const emit = defineEmits(["update", "toggle-map"]);

const toast = useToast();

const searchQuery = ref("");
const hits = ref(/** @type {Array<Record<string, unknown>>} */ ([]));
const open = ref(false);
const searching = ref(false);
const suggestFailed = ref(false);
/** После выбора подсказки не дёргаем suggest от программной записи searchQuery. */
let suppressSuggest = false;
let timer = /** @type {ReturnType<typeof setTimeout>|null} */ (null);
let requestToken = 0;

/**
 * @param {'city'|'street'|'house'|'entrance'|'floor'|'apartment'|'comment'} key
 */
function fieldModel(key) {
    return computed({
        get: () => props[key],
        set: (value) => patchManual({ [key]: String(value ?? "") }),
    });
}

const cityModel = fieldModel("city");
const streetModel = fieldModel("street");
const houseModel = fieldModel("house");
const entranceModel = fieldModel("entrance");
const floorModel = fieldModel("floor");
const apartmentModel = fieldModel("apartment");
const commentModel = fieldModel("comment");

const hasCoordinates = computed(() => {
    const lat = toNumberOrNull(props.latitude);
    const lng = toNumberOrNull(props.longitude);
    return lat !== null && lng !== null;
});

const coordsStatus = computed(() => {
    if (hasCoordinates.value) {
        return "Координаты получены";
    }
    if (String(props.street).trim() && String(props.house).trim()) {
        return "Нет координат — выбери подсказку или точку на карте";
    }
    return "Адрес не выбран";
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
 * @param {Record<string, unknown>} patch
 */
function emitAddress(patch) {
    emit("update", {
        city: patch.city !== undefined ? String(patch.city ?? "") : props.city,
        street: patch.street !== undefined ? String(patch.street ?? "") : props.street,
        house: patch.house !== undefined ? String(patch.house ?? "") : props.house,
        entrance: patch.entrance !== undefined ? String(patch.entrance ?? "") : props.entrance,
        floor: patch.floor !== undefined ? String(patch.floor ?? "") : props.floor,
        apartment: patch.apartment !== undefined ? String(patch.apartment ?? "") : props.apartment,
        comment: patch.comment !== undefined ? String(patch.comment ?? "") : props.comment,
        latitude: patch.latitude === undefined ? props.latitude : patch.latitude,
        longitude: patch.longitude === undefined ? props.longitude : patch.longitude,
    });
}

/**
 * Ручная правка: coords сбрасываем только если меняют город/улицу/дом.
 * Подъезд/этаж/кв/комментарий не трогают геокод.
 * @param {Record<string, string>} patch
 */
function patchManual(patch) {
    const keys = Object.keys(patch);
    const clearsCoords = keys.some((key) => key === "city" || key === "street" || key === "house");
    emitAddress({
        ...patch,
        ...(clearsCoords
            ? {
                latitude: "",
                longitude: "",
            }
            : {}),
    });
}

/**
 * @param {Record<string, unknown>} hit
 */
function pickSuggestion(hit) {
    const city = String(hit.city ?? props.branchCity ?? "");
    const street = String(hit.street ?? "");
    const house = String(hit.house ?? "");
    const entrance = String(hit.entrance ?? "");
    const floor = String(hit.floor ?? "");
    const apartment = String(hit.apartment ?? "");
    const lat = toNumberOrNull(hit.lat);
    const lng = toNumberOrNull(hit.lng);

    if (timer) {
        clearTimeout(timer);
        timer = null;
    }
    requestToken += 1;
    searching.value = false;
    open.value = false;
    hits.value = [];
    suppressSuggest = true;
    searchQuery.value = String(hit.value ?? [city, street, house].filter(Boolean).join(", "));

    const payload = {
        city,
        street,
        house,
        entrance,
        floor,
        apartment,
        // комментарий ДаДата не даёт — не затираем руками введённый
        latitude: lat === null ? "" : lat,
        longitude: lng === null ? "" : lng,
    };

    if (!street || !house) {
        toast.error("В подсказке нет улицы или дома — уточни запрос");
        emitAddress(payload);
        return;
    }

    if (lat === null || lng === null) {
        toast.error("У этой подсказки нет координат — выбери другую или карту");
        emitAddress(payload);
        return;
    }

    emitAddress(payload);
}

async function runSuggest(query) {
    const text = String(query ?? "").trim();
    if (text.length < 2) {
        hits.value = [];
        open.value = false;
        searching.value = false;
        return;
    }

    const token = ++requestToken;
    searching.value = true;
    try {
        const list = await suggestAddressFromDaData({
            query: text,
            city: props.branchCity || null,
            limit: 8,
        });
        if (token !== requestToken) {
            return;
        }
        suggestFailed.value = false;
        hits.value = list;
        open.value = list.length > 0;
    } catch (err) {
        if (token !== requestToken) {
            return;
        }
        hits.value = [];
        open.value = false;
        if (!suggestFailed.value) {
            suggestFailed.value = true;
            toast.error(err instanceof Error ? err.message : "Подсказки временно недоступны");
        }
    } finally {
        if (token === requestToken) {
            searching.value = false;
        }
    }
}

watch(searchQuery, (value) => {
    if (suppressSuggest) {
        suppressSuggest = false;
        if (timer) {
            clearTimeout(timer);
            timer = null;
        }
        return;
    }
    if (timer) {
        clearTimeout(timer);
    }
    timer = setTimeout(() => runSuggest(value), 280);
});

function onSearchFocus() {
    if (hits.value.length > 0) {
        open.value = true;
    }
}

onBeforeUnmount(() => {
    if (timer) {
        clearTimeout(timer);
    }
    requestToken += 1;
});
</script>

<template>
    <div class="space-y-3">
        <UiFormField label="Поиск адреса">
            <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-start">
                <div class="relative min-w-0 flex-1">
                    <input
                        v-model="searchQuery"
                        type="text"
                        autocomplete="off"
                        placeholder="Начни вводить улицу и дом…"
                        class="h-11 w-full border border-bo-border bg-bo-field px-4 text-sm text-bo-fg transition-colors placeholder:text-bo-muted/60 hover:border-bo-border-strong focus:border-bo-accent focus:outline-none"
                        @focus="onSearchFocus"
                    >
                    <ul
                        v-if="open && hits.length > 0"
                        class="absolute z-20 mt-1 max-h-56 w-full overflow-auto border border-bo-border bg-bo-elevated shadow-bo"
                    >
                        <li
                            v-for="(hit, index) in hits"
                            :key="`${hit.value}-${index}`"
                            class="cursor-pointer px-3 py-2 text-sm text-bo-fg hover:bg-white/10"
                            @mousedown.prevent="pickSuggestion(hit)"
                        >
                            {{ hit.value }}
                        </li>
                    </ul>
                </div>
                <UiButton
                    class="!shrink-0 !bg-white/10 !text-bo-fg sm:!px-4"
                    @click="emit('toggle-map')"
                >
                    {{ mapOpen ? 'Скрыть карту' : 'Указать на карте' }}
                </UiButton>
            </div>
            <p
                v-if="searching"
                class="mt-1 text-xs text-bo-muted"
            >
                Ищем…
            </p>
        </UiFormField>

        <div class="grid gap-3 sm:grid-cols-2">
            <UiFormField label="Город">
                <UiInput v-model="cityModel" />
            </UiFormField>
            <UiFormField
                label="Улица"
                required
                :error="streetError"
            >
                <UiInput v-model="streetModel" />
            </UiFormField>
            <UiFormField
                label="Дом"
                required
                :error="houseError"
            >
                <UiInput v-model="houseModel" />
            </UiFormField>
            <UiFormField label="Подъезд">
                <UiInput v-model="entranceModel" />
            </UiFormField>
            <UiFormField label="Этаж">
                <UiInput v-model="floorModel" />
            </UiFormField>
            <UiFormField label="Квартира">
                <UiInput v-model="apartmentModel" />
            </UiFormField>
        </div>

        <UiFormField label="Комментарий к адресу">
            <UiInput v-model="commentModel" />
        </UiFormField>

        <p class="text-xs text-bo-muted">
            {{ coordsStatus }}
            <template v-if="city">
                · {{ city }}
            </template>
        </p>
        <p
            v-if="zoneStatus"
            class="text-xs"
            :class="zoneStatus.includes('вне') ? 'text-red-300' : 'text-bo-muted'"
        >
            {{ zoneStatus }}
        </p>
    </div>
</template>
