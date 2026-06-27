<script setup>
import { computed } from "vue";
import { useAppDesign } from "../../design/useAppDesign";

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
    selected: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["select"]);

const c = useAppDesign().components.checkout.cart;
const d = useAppDesign().components.catalog.cards.desktop;
const cs = useAppDesign().components.catalog.cards.shared;

const thumbUrl = computed(() => {
    const raw = props.item.imageUrl ?? props.item.image_url;
    if (raw == null) {
        return null;
    }

    const url = String(raw).trim();

    return url !== "" ? url : null;
});

const compositionLine = computed(() => {
    const parts = Array.isArray(props.item.composition)
        ? props.item.composition.map((part) => String(part).trim()).filter(Boolean)
        : [];

    return parts.length > 0 ? parts.join(" · ") : null;
});

function handleSelect() {
    if (props.disabled) {
        return;
    }

    emit("select", props.item);
}
</script>

<template>
    <button
        type="button"
        :class="[
            c.giftCandidateCard,
            selected ? c.giftCandidateCardSelected : c.giftCandidateCardIdle,
            disabled ? 'cursor-not-allowed opacity-60' : '',
        ]"
        :disabled="disabled"
        :aria-pressed="selected"
        @click="handleSelect"
    >
        <span
            v-if="selected"
            :class="c.giftCandidateBadge"
        >
            Выбран
        </span>

        <div :class="c.giftCandidateThumbCol">
            <img
                v-if="thumbUrl"
                :src="thumbUrl"
                :alt="item.name || `Товар #${item.id}`"
                :class="d.img"
                loading="lazy"
            />
            <div
                v-else
                :class="d.placeholder"
            >
                {{ cs.noPhotoText }}
            </div>
        </div>

        <div :class="c.giftCandidateBody">
            <p :class="c.giftCandidateTitle">
                {{ item.name || `Товар #${item.id}` }}
            </p>
            <p
                v-if="compositionLine"
                :class="c.giftCandidateComposition"
            >
                {{ compositionLine }}
            </p>
        </div>
    </button>
</template>
