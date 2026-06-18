<script setup>
import { computed } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { formatMoneyRublesRu } from "../../utils/moneyFormat";

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
const cs = useAppDesign().components.catalog.cards.shared;

const compositionLine = computed(() => {
    const parts = Array.isArray(props.item.composition)
        ? props.item.composition.map((part) => String(part).trim()).filter(Boolean)
        : [];

    return parts.length > 0 ? parts.join(" · ") : null;
});

function formatPrice(value) {
    return formatMoneyRublesRu(value);
}

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
                v-if="item.imageUrl"
                :src="item.imageUrl"
                :alt="item.name || `Товар #${item.id}`"
                :class="c.giftCandidateThumbImg"
                loading="lazy"
            />
            <div
                v-else
                :class="c.giftCandidateThumbPlaceholder"
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
            <p :class="c.giftCandidatePrice">
                В меню {{ formatPrice(item.priceRub) }} ₽ · в заказе 0 ₽
            </p>
        </div>
    </button>
</template>
