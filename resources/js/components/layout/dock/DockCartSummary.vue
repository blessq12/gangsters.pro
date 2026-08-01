<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue";
import { useAppDesign } from "../../../design/useAppDesign";
import { DOMAIN_EVENTS, subscribeDomainEvent } from "../../../shared/domainEvents";
import { useCheckoutStore } from "../../../stores/checkoutStore";
import { useUiStore } from "../../../stores/uiStore";
import { formatMoneyRublesRu } from "../../../utils/moneyFormat";

const FLASH_MS = 1000;

const emit = defineEmits({
    toggle: () => true,
});

const ds = useAppDesign().components.dock.cartSummary;
const cartStore = useCheckoutStore();
const uiStore = useUiStore();

const isEmpty = computed(() => !cartStore.hasCartItems);
const isActive = computed(() => uiStore.dockActiveId === "cart");
const justAdded = ref(false);

let flashTimer = null;
let unsubscribeAdd = null;

const amountLabel = computed(
    () => `${formatMoneyRublesRu(cartStore.cartTotalAmount)} ₽`,
);

const qtyLabel = computed(() => `${cartStore.cartTotalItems} шт`);

const rootClass = computed(() => {
    if (justAdded.value) {
        return [ds.root, ds.rootFlash];
    }
    return [ds.root, isActive.value ? ds.rootActive : ds.rootInactive];
});

function clearFlashTimer() {
    if (flashTimer != null) {
        clearTimeout(flashTimer);
        flashTimer = null;
    }
}

function showAddedFlash() {
    justAdded.value = true;
    clearFlashTimer();
    flashTimer = setTimeout(() => {
        justAdded.value = false;
        flashTimer = null;
    }, FLASH_MS);
}

function onClick() {
    emit("toggle");
}

onMounted(() => {
    unsubscribeAdd = subscribeDomainEvent(
        DOMAIN_EVENTS.CART_ADD_REQUESTED,
        () => {
            showAddedFlash();
        },
    );
});

onUnmounted(() => {
    clearFlashTimer();
    if (unsubscribeAdd) {
        unsubscribeAdd();
        unsubscribeAdd = null;
    }
});
</script>

<template>
    <button
        type="button"
        :class="rootClass"
        :aria-label="
            justAdded
                ? 'Добавлено'
                : isEmpty
                  ? 'Корзина пуста'
                  : `Корзина: ${amountLabel}, ${qtyLabel}`
        "
        :aria-pressed="isActive"
        data-dock-target="cart"
        @click="onClick"
    >
        <span :class="ds.sheen" aria-hidden="true" />

        <span :class="ds.content">
            <span
                :class="[
                    ds.idleLayer,
                    justAdded ? ds.idleLayerHidden : '',
                ]"
            >
                <template v-if="isEmpty">
                    <span :class="ds.emptyWrap">
                        <i :class="ds.emptyIcon" aria-hidden="true" />
                        <span :class="ds.emptyLabel">Корзина пуста</span>
                    </span>
                </template>
                <template v-else>
                    <span :class="ds.amount">{{ amountLabel }}</span>
                    <span :class="ds.qty">{{ qtyLabel }}</span>
                </template>
            </span>

            <Transition name="dock-cart-added">
                <span
                    v-if="justAdded"
                    :class="ds.flashLayer"
                >
                    <span :class="ds.flashLabel">добавлено</span>
                </span>
            </Transition>
        </span>
    </button>
</template>

<style scoped>
.dock-cart-summary-sheen::before {
    content: "";
    position: absolute;
    inset: 0;
    width: 45%;
    background: linear-gradient(
        105deg,
        transparent 0%,
        rgba(255, 255, 255, 0.08) 35%,
        rgba(255, 255, 255, 0.28) 50%,
        rgba(255, 255, 255, 0.08) 65%,
        transparent 100%
    );
    transform: translateX(-140%) skewX(-18deg);
    animation: dock-cart-summary-sheen-move 2.8s ease-in-out infinite;
}

@keyframes dock-cart-summary-sheen-move {
    0% {
        transform: translateX(-140%) skewX(-18deg);
    }
    55%,
    100% {
        transform: translateX(260%) skewX(-18deg);
    }
}

.dock-cart-added-enter-active,
.dock-cart-added-leave-active {
    transition: transform 0.22s ease;
}

.dock-cart-added-enter-from {
    transform: translateY(-100%);
}

.dock-cart-added-leave-to {
    transform: translateY(100%);
}

.dock-cart-added-enter-to,
.dock-cart-added-leave-from {
    transform: translateY(0);
}

@media (prefers-reduced-motion: reduce) {
    .dock-cart-summary-sheen::before {
        animation: none;
        display: none;
    }

    .dock-cart-added-enter-active,
    .dock-cart-added-leave-active {
        transition: none;
    }
}
</style>
