<script setup>
import { computed, ref } from "vue";
import { useAppDesign } from "../../../../design/useAppDesign";
import { useCheckoutSession } from "../../../../modules/checkout/application/session";
import { useCheckoutStore } from "../../../../modules/checkout/store";
import { useFavoritesStore } from "../../../../modules/client/store/favoritesStore";
import { formatMoneyRublesRu } from "../../../../platform/moneyFormat";

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
});

const panels = useAppDesign().components.dockPanels;
const s = panels.shared;
const f = panels.favorites;

const cartStore = useCheckoutStore();
const checkoutSession = useCheckoutSession();
const favoritesStore = useFavoritesStore();

const productId = computed(() => props.item.productId);
const qtyInCart = computed(() => checkoutSession.quantityByProduct(productId.value));

const qtyPulse = ref(false);
let qtyPulseTimer = null;
const QTY_PULSE_MS = 420;

function runQtyPulse() {
    qtyPulse.value = true;
    if (qtyPulseTimer) clearTimeout(qtyPulseTimer);
    qtyPulseTimer = setTimeout(() => {
        qtyPulse.value = false;
    }, QTY_PULSE_MS);
}

function handleAddToCart() {
    const snapshot = props.item?.productSnapshot;
    if (!snapshot?.id) return;
    void cartStore.addToCart(snapshot, 1).then(runQtyPulse);
}

function handleIncrement() {
    if (!productId.value) return;
    void cartStore.incrementCart(productId.value).then(runQtyPulse);
}

function handleDecrement() {
    if (!productId.value) return;
    void cartStore.decrementCart(productId.value);
}

const formatPrice = (value) => formatMoneyRublesRu(value);
</script>

<template>
    <li :class="f.row">
        <div :class="s.minWidth0">
            <p :class="f.productName">
                {{ item.productSnapshot?.name || `Товар #${item.productId}` }}
            </p>
            <p :class="f.productPrice">
                {{ formatPrice(item.productSnapshot?.price) }} ₽
            </p>
        </div>

        <div :class="f.actionRow">
            <button
                v-if="qtyInCart === 0"
                type="button"
                :class="f.actionAddToCart"
                @click="handleAddToCart"
            >
                В корзину
            </button>

            <div
                v-else
                :class="f.qtyBar"
                :aria-label="`В корзине ${qtyInCart} шт`"
            >
                <button
                    type="button"
                    :class="f.qtyBtn"
                    aria-label="Уменьшить количество"
                    @click="handleDecrement"
                >
                    –
                </button>
                <span
                    :class="[
                        f.qtyLabel,
                        qtyPulse ? f.qtyLabelPulse : '',
                    ]"
                >
                    {{ qtyInCart }} шт
                </span>
                <button
                    type="button"
                    :class="f.qtyBtn"
                    aria-label="Увеличить количество"
                    @click="handleIncrement"
                >
                    +
                </button>
            </div>

            <button
                type="button"
                :class="f.actionRemove"
                @click="favoritesStore.removeFavorite(item.productId)"
            >
                Убрать
            </button>
        </div>
    </li>
</template>

<style scoped></style>
