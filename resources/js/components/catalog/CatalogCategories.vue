<script setup>
import { useAppDesign } from "../../design/useAppDesign";

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    modelValue: {
        type: [Number, String, null],
        default: null,
    },
    allLabel: {
        type: String,
        default: "Все",
    },
    showAll: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(["update:modelValue", "change"]);

const dc = useAppDesign().components.catalog.categoriesLegacy;

function pillClasses(forValue) {
    const active = props.modelValue === forValue;
    return [
        dc.pillBase,
        active ? dc.pillActive : dc.pillInactive,
    ];
}

const handleSelect = (value) => {
    emit("update:modelValue", value);
    emit("change", value);
};
</script>

<template>
    <div :class="dc.outer">
        <div :class="dc.island">
            <div :class="dc.row">
                <button
                    v-if="showAll"
                    type="button"
                    :class="pillClasses(null)"
                    @click="handleSelect(null)"
                >
                    {{ allLabel }}
                </button>

                <button
                    v-for="category in categories"
                    :key="category.id ?? category.uri"
                    type="button"
                    :class="pillClasses(category.id ?? category.uri)"
                    @click="handleSelect(category.id ?? category.uri)"
                >
                    {{ category.name }}
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
