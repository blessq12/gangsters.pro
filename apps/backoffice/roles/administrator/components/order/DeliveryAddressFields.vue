<script setup>
import { computed, ref, watch } from "vue";
import {
    deliveryAddressFromDaDataHit,
    formatAddressSearchLabel,
    toCoordOrNull,
} from "../../../../shell/geo/addressContract.js";
import AddressSuggestField from "../../../../shell/components/address/AddressSuggestField.vue";
import { useToast } from "../../../../shell/composables/useToast.js";
import UiFormField from "../../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../../shell/components/ui/UiInput.vue";

const props = defineProps({
    street: { type: String, default: "" },
    house: { type: String, default: "" },
    entrance: { type: String, default: "" },
    floor: { type: String, default: "" },
    apartment: { type: String, default: "" },
    comment: { type: String, default: "" },
    latitude: { type: [String, Number], default: "" },
    longitude: { type: [String, Number], default: "" },
    /** Город филиала — единственный источник города + bias ДаДата. */
    branchCity: { type: String, default: "" },
    streetError: { type: String, default: "" },
    houseError: { type: String, default: "" },
    /** Статус зоны из quote после выбора адреса. */
    zoneStatus: { type: String, default: "" },
});

const emit = defineEmits(["update"]);

const toast = useToast();
const suggestDraft = ref("");

/**
 * @param {'street'|'house'|'entrance'|'floor'|'apartment'|'comment'} key
 */
function fieldModel(key) {
    return computed({
        get: () => props[key],
        set: (value) => patchManual({ [key]: String(value ?? "") }),
    });
}

const streetModel = fieldModel("street");
const houseModel = fieldModel("house");
const entranceModel = fieldModel("entrance");
const floorModel = fieldModel("floor");
const apartmentModel = fieldModel("apartment");
const commentModel = fieldModel("comment");

const hasCoordinates = computed(() => {
    const lat = toCoordOrNull(props.latitude);
    const lng = toCoordOrNull(props.longitude);
    return lat !== null && lng !== null;
});

const searchLabel = computed(() => {
    const street = String(props.street || "").trim();
    const house = String(props.house || "").trim();
    if (!street && !house) {
        return "";
    }
    return formatAddressSearchLabel({
        city: props.branchCity,
        street,
        house,
    });
});

const suggestModel = computed({
    get: () => {
        if (hasCoordinates.value) {
            return searchLabel.value;
        }
        return suggestDraft.value;
    },
    set: (value) => {
        suggestDraft.value = String(value ?? "");
    },
});

const coordsStatus = computed(() => {
    if (hasCoordinates.value) {
        return "Координаты получены";
    }
    if (String(props.street).trim() && String(props.house).trim()) {
        return "Нет координат — выбери адрес из подсказок";
    }
    return "Адрес не выбран";
});

/**
 * @param {Record<string, unknown>} patch
 */
function emitAddress(patch) {
    emit("update", {
        city: props.branchCity,
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
 * @param {Record<string, string>} patch
 */
function patchManual(patch) {
    const keys = Object.keys(patch);
    const clearsCoords = keys.some((key) => key === "street" || key === "house");
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
function onSelectSuggestion(hit) {
    const address = deliveryAddressFromDaDataHit(hit, props.branchCity);
    const payload = {
        street: address.street,
        house: address.house,
        entrance: address.entrance || "",
        floor: address.floor || "",
        apartment: address.apartment || "",
        latitude: address.latitude === null ? "" : address.latitude,
        longitude: address.longitude === null ? "" : address.longitude,
    };

    if (!address.street || !address.house) {
        toast.error("В подсказке нет улицы или дома — уточни запрос");
        emitAddress(payload);
        return;
    }

    if (address.latitude === null || address.longitude === null) {
        toast.error("У этой подсказки нет координат — выбери другую");
        emitAddress(payload);
        return;
    }

    suggestDraft.value = formatAddressSearchLabel(hit);
    emitAddress(payload);
}

watch(
    () => [props.branchCity, props.street, props.house, props.latitude, props.longitude],
    () => {
        if (hasCoordinates.value) {
            suggestDraft.value = searchLabel.value;
        }
    },
    { immediate: true },
);
</script>

<template>
    <div class="space-y-3">
        <p v-if="branchCity" class="text-xs text-bo-muted">Город: {{ branchCity }}</p>

        <AddressSuggestField
            v-model="suggestModel"
            :city="branchCity"
            @select="onSelectSuggestion"
        />

        <div class="grid gap-3 sm:grid-cols-2">
            <UiFormField label="Улица" required :error="streetError">
                <UiInput v-model="streetModel" />
            </UiFormField>
            <UiFormField label="Дом" required :error="houseError">
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
