<script setup>
import { computed, ref, watch } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useAddressSuggest } from "../../composables/useAddressSuggest.js";

const props = defineProps({
    /** Город филиала — обязателен для ДаДата. */
    city: { type: String, required: true },
    modelValue: { type: String, default: "" },
    disabled: { type: Boolean, default: false },
    placeholder: { type: String, default: "Улица, дом…" },
});

const emit = defineEmits(["update:modelValue", "select"]);

const s = useAppDesign().components.checkout.shared;
const cityRef = computed(() => String(props.city || "").trim());
const errorText = ref("");

const { searchQuery, hits, open, searching, scheduleSuggest, pickAndClose, setSearchLabel } =
    useAddressSuggest({
        city: cityRef,
        onError: (err) => {
            errorText.value = err.message || "Подсказки недоступны";
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
    errorText.value = "";
    if (!props.disabled) {
        scheduleSuggest(value);
    }
});

/**
 * @param {Record<string, unknown>} hit
 */
function onPick(hit) {
    const label = String(hit.value ?? "");
    pickAndClose(label);
    emit("update:modelValue", label);
    emit("select", hit);
}

function onInput(event) {
    searchQuery.value = event.target.value;
}
</script>

<template>
    <div class="relative">
        <input
            :value="searchQuery"
            type="text"
            autocomplete="off"
            :placeholder="placeholder"
            :disabled="disabled || !city"
            :class="s.inputFieldFull"
            @input="onInput"
            @focus="open = hits.length > 0"
        />
        <ul
            v-if="open && hits.length > 0"
            class="absolute z-20 mt-1 max-h-56 w-full overflow-auto border border-white/10 bg-zinc-950 py-1 text-sm shadow-lg"
        >
            <li v-for="(hit, idx) in hits" :key="`${hit.value}-${idx}`">
                <button
                    type="button"
                    class="block w-full px-3 py-2 text-left hover:bg-white/10"
                    @mousedown.prevent="onPick(hit)"
                >
                    {{ hit.value }}
                </button>
            </li>
        </ul>
        <p v-if="!city" class="mt-1 text-xs text-red-300">
            Нет города филиала — подсказки недоступны
        </p>
        <p v-else-if="searching" class="mt-1 text-xs opacity-60">Ищем…</p>
        <p v-else-if="errorText" class="mt-1 text-xs text-red-300">
            {{ errorText }}
        </p>
    </div>
</template>
