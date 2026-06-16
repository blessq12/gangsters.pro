<script setup>
import { useAppDesign } from "../../design/useAppDesign";

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    isSet: {
        type: Boolean,
        default: false,
    },
    setCountLabel: {
        type: String,
        default: null,
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

function tagToneClass(color) {
    const c = String(color || "").trim().toLowerCase();
    return cs.tagTone[c] ?? cs.tagTone.default;
}
</script>

<template>
    <div :class="tokens.badgesCol">
        <span
            v-if="isSet"
            :class="cs.setBadge"
        >
            Набор
        </span>
        <div
            v-if="isSet && setCountLabel"
            :class="cs.setCountPill"
        >
            {{ setCountLabel }}
        </div>
        <div
            v-else-if="product.weight"
            :class="tokens.weightPill"
        >
            {{ product.weight }} г
        </div>
        <span
            v-if="primaryTag"
            :class="[tokens.tagPill, tagToneClass(primaryTag.color)]"
        >
            {{ primaryTag.label }}
        </span>
    </div>
</template>
