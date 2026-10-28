<script setup>
import { computed, ref } from "vue";
import { formatMoneyRublesRu } from "../../platform/moneyFormat";
import { useAppDesign } from "../../design/useAppDesign";

const props = defineProps({
    price: {
        type: Number,
        default: null,
    },
    qtyInCart: {
        type: Number,
        default: 0,
    },
    variant: {
        type: String,
        required: true,
        validator: (value) => ["desktop", "mobileGrid", "horizontal"].includes(value),
    },
    compact: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["add-to-cart", "increment", "decrement"]);

const cards = useAppDesign().components.catalog.cards;
const commerce = cards.commerce;

const tokens = computed(() => {
    if (props.variant === "desktop") return cards.desktop;
    if (props.variant === "horizontal") return cards.horizontalMobile;
    return cards.mobileGrid;
});

const rootClass = computed(() => {
    const base = commerce.root[props.variant];
    if (props.variant === "desktop" && props.compact) {
        return `${base} ${commerce.root.desktopCompact}`;
    }
    return base;
});

const addButtonClass = computed(() => {
    if (props.variant === "horizontal") {
        return tokens.value.cartAddIconBtn;
    }
    if (props.variant === "desktop" && props.compact) {
        return tokens.value.cartAddIconBtn;
    }
    if (props.variant === "desktop") {
        return tokens.value.addBtn;
    }
    return tokens.value.cartAddText;
});

const showAddIcon = computed(
    () =>
        props.variant === "horizontal"
        || props.variant === "mobileGrid"
        || (props.variant === "desktop" && props.compact),
);

const showAddLabel = computed(
    () =>
        props.variant === "desktop" && !props.compact
        || props.variant === "mobileGrid",
);

const qtyBarClass = computed(() => tokens.value.qtyBar || tokens.value.qtyCluster);
const qtyBtnClass = computed(() => tokens.value.qtyBtn || tokens.value.qtyMiniBtn);
const qtyLabelClass = computed(() => tokens.value.qtyLabel || tokens.value.qtyNum);

const formattedPrice = computed(() =>
    props.price != null ? `${formatMoneyRublesRu(props.price)}\u00a0₽` : null,
);

const justAddedToCart = ref(false);
const justChangedQty = ref(false);
let justAddedTimer = null;
let justQtyTimer = null;
const FEEDBACK_ANIM_MS = 780;

function pulseAddedToCart() {
    if (props.variant !== "mobileGrid") return;
    justAddedToCart.value = true;
    if (justAddedTimer) clearTimeout(justAddedTimer);
    justAddedTimer = setTimeout(() => {
        justAddedToCart.value = false;
    }, FEEDBACK_ANIM_MS);
}

function pulseQty() {
    if (props.variant !== "mobileGrid") return;
    justChangedQty.value = true;
    if (justQtyTimer) clearTimeout(justQtyTimer);
    justQtyTimer = setTimeout(() => {
        justChangedQty.value = false;
    }, FEEDBACK_ANIM_MS);
}

function handleAddToCart() {
    emit("add-to-cart");
    pulseAddedToCart();
}

function handleIncrement() {
    emit("increment");
    pulseQty();
}

function handleDecrement() {
    emit("decrement");
    pulseQty();
}
</script>

<template>
    <div :class="rootClass">
        <template v-if="qtyInCart === 0">
            <button
                type="button"
                :class="[
                    addButtonClass,
                    variant === 'mobileGrid' && justAddedToCart ? 'scale-[1.06]' : 'scale-100',
                ]"
                aria-label="Добавить в корзину"
                @click.stop="handleAddToCart"
            >
                <span
                    v-if="variant === 'mobileGrid'"
                    :class="[tokens.feedbackCartRing, { 'pc-feedback-ring--active': justAddedToCart }]"
                    aria-hidden="true"
                />
                <i
                    v-if="showAddIcon"
                    :class="[
                        tokens.cartAddIcon,
                        variant === 'mobileGrid' && justAddedToCart ? 'scale-110' : 'scale-100',
                    ]"
                />
                <span v-if="showAddLabel">В корзину</span>
            </button>

            <span
                v-if="formattedPrice"
                :class="commerce.priceIdle[variant]"
            >
                {{ formattedPrice }}
            </span>
        </template>

        <template v-else>
            <div
                :class="[
                    qtyBarClass,
                    variant === 'mobileGrid' && justChangedQty ? 'scale-[1.04]' : 'scale-100',
                ]"
            >
                <button
                    type="button"
                    :class="qtyBtnClass"
                    aria-label="Уменьшить количество"
                    @click.stop="handleDecrement"
                >
                    –
                </button>
                <span :class="qtyLabelClass">
                    <template v-if="variant === 'desktop'">{{ qtyInCart }} шт</template>
                    <template v-else>{{ qtyInCart }}</template>
                </span>
                <button
                    type="button"
                    :class="qtyBtnClass"
                    aria-label="Увеличить количество"
                    @click.stop="handleIncrement"
                >
                    +
                </button>
            </div>

            <span
                v-if="formattedPrice"
                :class="commerce.priceInCart[variant]"
            >
                {{ formattedPrice }}
            </span>
        </template>
    </div>
</template>

<style scoped>
.pc-feedback-ring {
    opacity: 0;
    box-shadow: none;
    transform: scale(0.92);
}

.pc-feedback-ring--active {
    animation: pc-feedback-ring-cart 0.78s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}

@keyframes pc-feedback-ring-cart {
    0% {
        opacity: 0;
        transform: scale(0.85);
    }
    42% {
        opacity: 1;
        transform: scale(1);
    }
    100% {
        opacity: 0;
        transform: scale(1.08);
    }
}
</style>
