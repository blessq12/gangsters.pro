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
    variant: {
        type: String,
        default: "desktop", // desktop | mobile
    },
});

const emit = defineEmits(["update:modelValue", "change"]);

const dc = useAppDesign().components.catalog.categories;

const isMobile = () => props.variant === "mobile";

function pillClasses(forValue) {
    const active =
        props.modelValue === forValue;
    const mob = isMobile();
    const base = [dc.pillBase, mob ? dc.pillSizingMobile : dc.pillSizingDesktop];
    if (active) {
        base.push(mob ? dc.pillActiveMobile : dc.pillActiveDesktop);
    } else {
        base.push(dc.pillInactive);
    }
    return base;
}

const handleSelect = (value) => {
    emit("update:modelValue", value);
    emit("change", value);
};
</script>

<template>
    <div :class="dc.outer">
        <div
            :class="[
                dc.island,
                isMobile() ? dc.islandPaddingMobile : dc.islandPaddingDesktop,
            ]"
        >
            <div
                :class="isMobile() ? dc.rowMobile : dc.rowDesktop"
            >
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

<style scoped>
.cats-scroll {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.cats-scroll::-webkit-scrollbar {
    width: 0;
    height: 0;
    display: none;
}
</style>
