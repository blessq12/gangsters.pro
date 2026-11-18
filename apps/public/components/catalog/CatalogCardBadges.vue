<script setup>
import { computed } from "vue";
import { useAppDesign } from "../../design/useAppDesign";

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    primaryTag: {
        type: Object,
        default: null,
    },
    variant: {
        type: String,
        default: "desktop",
        validator: (value) => ["desktop", "mobileGrid"].includes(value),
    },
});

const cs = useAppDesign().components.catalog.cards.shared;
const tokens = useAppDesign().components.catalog.cards[props.variant === "desktop" ? "desktop" : "mobileGrid"];

const displayWeight = computed(() => {
    const grams = Number(props.product?.weight);
    if (!Number.isFinite(grams) || grams <= 0) return null;
    return Math.round(grams);
});

function tagToneClass(color) {
    const c = String(color || "").trim().toLowerCase();
    return cs.tagTone[c] ?? cs.tagTone.default;
}
</script>

<template>
    <div :class="tokens.badgesCol">
        <div
            v-if="displayWeight != null"
            :class="tokens.weightPill"
        >
            {{ displayWeight }}&nbsp;г
        </div>
        <span
            v-if="primaryTag"
            :class="[tokens.tagPill, tagToneClass(primaryTag.color)]"
        >
            {{ primaryTag.label }}
        </span>
    </div>
</template>
