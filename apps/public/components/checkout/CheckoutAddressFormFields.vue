<script setup>
import { computed, onBeforeUnmount, ref, watch } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { suggestAddressFromDaData } from "../../geo/dadataSuggest.js";
import FormField from "../ui/FormField.vue";

const props = defineProps({
    street: { type: String, default: "" },
    house: { type: String, default: "" },
    entrance: { type: String, default: "" },
    floor: { type: String, default: "" },
    apartment: { type: String, default: "" },
    latitude: { type: [String, Number], default: null },
    longitude: { type: [String, Number], default: null },
    branchCity: { type: String, default: "" },
    streetError: { type: String, default: null },
    houseError: { type: String, default: null },
    showTitle: { type: Boolean, default: false },
    title: { type: String, default: "" },
    showComment: { type: Boolean, default: false },
    comment: { type: String, default: "" },
    commentPlaceholder: {
        type: String,
        default: "Комментарий для курьера (подъезд, код, ориентир)",
    },
    showDefaultCheckbox: { type: Boolean, default: false },
    makeDefault: { type: Boolean, default: false },
});

const emit = defineEmits([
    "update:street",
    "update:house",
    "update:entrance",
    "update:floor",
    "update:apartment",
    "update:title",
    "update:comment",
    "update:makeDefault",
    "apply-suggestion",
    "clear-coords",
]);

const s = useAppDesign().components.checkout.shared;

const searchQuery = ref("");
const hits = ref(/** @type {Array<Record<string, unknown>>} */ ([]));
const open = ref(false);
const searching = ref(false);
const suggestError = ref("");
/** @type {ReturnType<typeof setTimeout>|null} */
let timer = null;
let requestToken = 0;
let suppressSuggest = false;

const hasCoordinates = computed(() => {
    const lat = Number(props.latitude);
    const lng = Number(props.longitude);
    return Number.isFinite(lat) && Number.isFinite(lng);
});

const coordsHint = computed(() => {
    if (hasCoordinates.value) {
        return "Координаты получены";
    }
    if (String(props.street).trim() && String(props.house).trim()) {
        return "Нет координат — выбери адрес из подсказок";
    }
    return "Начни вводить адрес — выбери подсказку";
});

function onStreetInput(value) {
    emit("update:street", value);
    emit("clear-coords");
}

function onHouseInput(value) {
    emit("update:house", value);
    emit("clear-coords");
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
    suggestError.value = "";
    try {
        const rows = await suggestAddressFromDaData({
            query: text,
            city: props.branchCity,
            limit: 8,
        });
        if (token !== requestToken) {
            return;
        }
        hits.value = rows;
        open.value = rows.length > 0;
    } catch (err) {
        if (token !== requestToken) {
            return;
        }
        hits.value = [];
        open.value = false;
        suggestError.value = err instanceof Error ? err.message : "Подсказки недоступны";
    } finally {
        if (token === requestToken) {
            searching.value = false;
        }
    }
}

function scheduleSuggest(query) {
    if (suppressSuggest) {
        suppressSuggest = false;
        return;
    }
    if (timer) {
        clearTimeout(timer);
    }
    timer = setTimeout(() => {
        timer = null;
        runSuggest(query);
    }, 280);
}

watch(searchQuery, (value) => {
    scheduleSuggest(value);
});

/**
 * @param {Record<string, unknown>} hit
 */
function pickSuggestion(hit) {
    if (timer) {
        clearTimeout(timer);
        timer = null;
    }
    requestToken += 1;
    searching.value = false;
    open.value = false;
    hits.value = [];
    suppressSuggest = true;
    searchQuery.value = String(hit.value ?? "");
    emit("apply-suggestion", hit);
}

onBeforeUnmount(() => {
    if (timer) {
        clearTimeout(timer);
    }
    requestToken += 1;
});
</script>

<template>
    <FormField
        v-if="showTitle"
        label="Название"
    >
        <template #default="{ id, invalidClass }">
            <input
                :id="id"
                :value="title"
                type="text"
                placeholder="Дом, работа"
                :class="[s.inputFieldFull, invalidClass]"
                @input="emit('update:title', $event.target.value)"
            />
        </template>
    </FormField>

    <FormField label="Поиск адреса">
        <template #default="{ id }">
            <div class="relative">
                <input
                    :id="id"
                    v-model="searchQuery"
                    type="text"
                    autocomplete="off"
                    placeholder="Город, улица, дом…"
                    :class="s.inputFieldFull"
                    @focus="open = hits.length > 0"
                />
                <ul
                    v-if="open && hits.length > 0"
                    class="absolute z-20 mt-1 max-h-56 w-full overflow-auto border border-white/10 bg-zinc-950 py-1 text-sm shadow-lg"
                >
                    <li
                        v-for="(hit, idx) in hits"
                        :key="`${hit.value}-${idx}`"
                    >
                        <button
                            type="button"
                            class="block w-full px-3 py-2 text-left hover:bg-white/10"
                            @mousedown.prevent="pickSuggestion(hit)"
                        >
                            {{ hit.value }}
                        </button>
                    </li>
                </ul>
            </div>
            <p
                v-if="searching"
                class="mt-1 text-xs opacity-60"
            >
                Ищем…
            </p>
            <p
                v-else-if="suggestError"
                class="mt-1 text-xs text-red-300"
            >
                {{ suggestError }}
            </p>
            <p
                v-else
                class="mt-1 text-xs opacity-60"
            >
                {{ coordsHint }}
            </p>
        </template>
    </FormField>

    <FormField
        label="Улица"
        :error="streetError"
    >
        <template #default="{ id, invalid, invalidClass, describedBy, ariaInvalid }">
            <input
                :id="id"
                :value="street"
                type="text"
                placeholder="Например, Ленина"
                :class="[s.inputFieldFull, invalid && invalidClass]"
                :aria-invalid="ariaInvalid"
                :aria-describedby="describedBy"
                @input="onStreetInput($event.target.value)"
            />
        </template>
    </FormField>

    <div :class="s.grid4">
        <FormField
            label="Дом"
            :error="houseError"
        >
            <template #default="{ id, invalid, invalidClass, describedBy, ariaInvalid }">
                <input
                    :id="id"
                    :value="house"
                    type="text"
                    placeholder="12"
                    :class="[s.inputFieldGridCell, invalid && invalidClass]"
                    :aria-invalid="ariaInvalid"
                    :aria-describedby="describedBy"
                    @input="onHouseInput($event.target.value)"
                />
            </template>
        </FormField>

        <FormField label="Подъезд">
            <template #default="{ id }">
                <input
                    :id="id"
                    :value="entrance"
                    type="text"
                    placeholder="1"
                    :class="s.inputFieldGridCell"
                    @input="emit('update:entrance', $event.target.value)"
                />
            </template>
        </FormField>

        <FormField label="Этаж">
            <template #default="{ id }">
                <input
                    :id="id"
                    :value="floor"
                    type="text"
                    placeholder="3"
                    :class="s.inputFieldGridCell"
                    @input="emit('update:floor', $event.target.value)"
                />
            </template>
        </FormField>

        <FormField label="Квартира">
            <template #default="{ id }">
                <input
                    :id="id"
                    :value="apartment"
                    type="text"
                    placeholder="45"
                    :class="s.inputFieldGridCell"
                    @input="emit('update:apartment', $event.target.value)"
                />
            </template>
        </FormField>
    </div>

    <FormField
        v-if="showComment"
        label="Комментарий к адресу"
    >
        <template #default="{ id }">
            <textarea
                :id="id"
                :value="comment"
                rows="2"
                :placeholder="commentPlaceholder"
                :class="s.textareaAddress"
                @input="emit('update:comment', $event.target.value)"
            />
        </template>
    </FormField>

    <label
        v-if="showDefaultCheckbox"
        :class="s.checkboxLabelRow"
    >
        <AppCheckbox
            :model-value="makeDefault"
            size="sm"
            @update:model-value="emit('update:makeDefault', $event)"
        />
        <span>Сделать основным адресом</span>
    </label>
</template>
