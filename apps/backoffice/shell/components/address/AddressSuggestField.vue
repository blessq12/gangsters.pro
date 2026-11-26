<script setup>
import { computed, watch } from "vue";
import { useAddressSuggest } from "../../composables/useAddressSuggest.js";
import { formatAddressSearchLabel } from "../../geo/addressContract.js";
import { useToast } from "../../composables/useToast.js";

const props = defineProps({
    /** Город филиала — обязателен для ДаДата. */
    city: { type: String, required: true },
    modelValue: { type: String, default: "" },
    disabled: { type: Boolean, default: false },
    placeholder: { type: String, default: "Начни вводить улицу и дом…" },
    label: { type: String, default: "Поиск адреса" },
});

const emit = defineEmits(["update:modelValue", "select"]);

const toast = useToast();

const cityRef = computed(() => String(props.city || "").trim());

const { searchQuery, hits, open, searching, scheduleSuggest, pickAndClose, setSearchLabel } =
    useAddressSuggest({
        city: cityRef,
        onError: (err) => {
            toast.error(err.message || "Подсказки временно недоступны");
        },
    });

watch(
    () => props.modelValue,
    (value) => {
        const next = String(value ?? "");
        if (searchQuery.value === next) {
            return;
        }
        setSearchLabel(next);
    },
    { immediate: true },
);

watch(searchQuery, (value) => {
    emit("update:modelValue", value);
    if (!props.disabled) {
        scheduleSuggest(value);
    }
});

/**
 * @param {Record<string, unknown>} hit
 */
function onPick(hit) {
    const label = formatAddressSearchLabel(hit);
    pickAndClose(label);
    emit("update:modelValue", label);
    emit("select", hit);
}

function onFocus() {
    if (hits.value.length > 0) {
        open.value = true;
    }
}

/**
 * @param {Event} event
 */
function onInput(event) {
    const target = /** @type {HTMLInputElement} */ (event.target);
    searchQuery.value = target.value;
}
</script>

<template>
    <div class="space-y-1">
        <div
            v-if="label"
            class="mb-2 block text-xs font-medium uppercase tracking-[0.1em] text-bo-muted"
        >
            {{ label }}
        </div>
        <div class="relative">
            <input
                :value="searchQuery"
                type="text"
                autocomplete="off"
                :placeholder="placeholder"
                :disabled="disabled || !city"
                class="h-11 w-full border border-bo-border bg-bo-field px-4 text-sm text-bo-fg transition-colors placeholder:text-bo-muted/60 hover:border-bo-border-strong focus:border-bo-accent focus:outline-none disabled:cursor-not-allowed disabled:opacity-40"
                @input="onInput"
                @focus="onFocus"
            />
            <ul
                v-if="open && hits.length > 0"
                class="absolute z-20 mt-1 max-h-56 w-full overflow-auto border border-bo-border bg-bo-elevated shadow-bo"
            >
                <li
                    v-for="(hit, index) in hits"
                    :key="`${hit.value}-${index}`"
                    class="cursor-pointer px-3 py-2 text-sm text-bo-fg hover:bg-white/10"
                    @mousedown.prevent="onPick(hit)"
                >
                    {{ hit.value }}
                </li>
            </ul>
        </div>
        <p v-if="!city" class="text-xs text-red-300">Нет города филиала — подсказки недоступны</p>
        <p v-else-if="searching" class="text-xs text-bo-muted">Ищем…</p>
    </div>
</template>
