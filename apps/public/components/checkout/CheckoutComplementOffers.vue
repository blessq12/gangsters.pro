<script setup>
import { storeToRefs } from "pinia";
import { computed } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useCatalogStore } from "../../modules/catalog/store";
import { useCheckoutFlowContext } from "../../modules/checkout/application/flowContext";
import {
    buildComplementOfferRows,
    useOrderPreview,
} from "../../modules/checkout/application/preview";
import { useCheckoutStore } from "../../modules/checkout/store";
import CheckoutSection from "./CheckoutSection.vue";

const chk = useAppDesign().components.checkout;
const c = chk.cart;
const s = chk.shared;

const { checkoutState } = useCheckoutFlowContext();
const { formatPrice } = checkoutState;

const catalogStore = useCatalogStore();
const cartStore = useCheckoutStore();
const { complementProducts } = storeToRefs(catalogStore);
const {
    complementLines,
    hasCartItems,
    hasRollsInCart,
    complement,
    showComplementProgress,
} = useOrderPreview();

const entitledSetCount = computed(
    () => Number(complement.value?.entitledSetCount) || 0,
);

const hasEntitledComplementSet = computed(() => {
    if (entitledSetCount.value > 0) {
        return true;
    }
    return complementLines.value.some(
        (line) => (Number(line?.quantity) || 0) > 0,
    );
});

/**
 * Комплектные всегда в корзине при наличии позиций:
 * бесплатные по акции + можно докупить тот же товар как обычную линию.
 */
const complementRows = computed(() => {
    if (!hasCartItems.value) {
        return [];
    }

    return buildComplementOfferRows(
        complementLines.value,
        complementProducts.value,
        { includeCatalogProducts: true },
    );
});

const showApproachProgress = computed(
    () =>
        hasCartItems.value
        && showComplementProgress.value
        && !hasEntitledComplementSet.value
        && hasRollsInCart.value
        && complementRows.value.length === 0,
);

const showBlock = computed(
    () => complementRows.value.length > 0 || showApproachProgress.value,
);

function paidQty(row) {
    return cartStore.cartQuantityByProduct(row.id);
}

function selectedFreeQty(row) {
    return cartStore.complementSelectionQty(row.id);
}

function displayQty(row) {
    return selectedFreeQty(row) + paidQty(row);
}

function showFreeBadge(row) {
    return selectedFreeQty(row) > 0 && paidQty(row) <= 0;
}

function unitPriceRub(row) {
    return Number(row.product?.price?.amount ?? row.product?.price) || 0;
}

function paidBadgeLabel(row) {
    const paid = paidQty(row);
    if (paid <= 0) {
        return "";
    }
    return `+${formatPrice(unitPriceRub(row) * paid)} ₽`;
}

function canDecrement(row) {
    return selectedFreeQty(row) > 0 || paidQty(row) > 0;
}

function canIncrementFree(row) {
    return selectedFreeQty(row) < entitledSetCount.value;
}

function canIncrementPaid(row) {
    return Boolean(row.product) && unitPriceRub(row) >= 0;
}

function canIncrement(row) {
    return canIncrementFree(row) || canIncrementPaid(row);
}

async function incrementProduct(row) {
    if (canIncrementFree(row)) {
        await cartStore.setComplementSelection(row.id, selectedFreeQty(row) + 1);
        return;
    }

    if (!row.product) {
        return;
    }

    if (paidQty(row) <= 0) {
        await cartStore.addToCart(row.product, 1);
        return;
    }

    await cartStore.incrementCart(row.id);
}

async function decrementProduct(row) {
    // Сначала платные (бьют в сумму), бесплатные — только после обнуления paid.
    if (paidQty(row) > 0) {
        await cartStore.decrementCart(row.id);
        return;
    }

    if (selectedFreeQty(row) > 0) {
        await cartStore.setComplementSelection(row.id, selectedFreeQty(row) - 1);
    }
}
</script>

<template>
    <CheckoutSection v-if="showBlock">
        <ul v-if="complementRows.length > 0" :class="s.offerCardGrid">
            <li
                v-for="row in complementRows"
                :key="`complement-offer:${row.id}`"
                :class="s.offerCardCompact"
            >
                <div :class="s.offerCardBody">
                    <div :class="s.offerCardTitleRow">
                        <p :class="[s.offerCardTitle, 'min-w-0 flex-1']">
                            {{ row.name }}
                        </p>
                        <span
                            v-if="showFreeBadge(row)"
                            :class="s.offerFreeBadge"
                        >
                            0 ₽
                        </span>
                        <span
                            v-else-if="paidBadgeLabel(row)"
                            :class="s.offerPaidBadge"
                        >
                            {{ paidBadgeLabel(row) }}
                        </span>
                        <span
                            v-else-if="unitPriceRub(row) > 0"
                            :class="s.offerPaidBadge"
                        >
                            {{ formatPrice(unitPriceRub(row)) }} ₽
                        </span>
                    </div>
                </div>

                <div
                    v-if="canIncrement(row) || displayQty(row) > 0"
                    :class="[c.qtyBar, 'shrink-0']"
                >
                    <button
                        type="button"
                        :class="c.qtyBtn"
                        :disabled="!canDecrement(row)"
                        @click="decrementProduct(row)"
                    >
                        –
                    </button>
                    <span :class="c.qtyLabel">
                        {{ displayQty(row) }}
                    </span>
                    <button
                        type="button"
                        :class="c.qtyBtn"
                        :disabled="!canIncrement(row)"
                        @click="incrementProduct(row)"
                    >
                        +
                    </button>
                </div>
            </li>
        </ul>
    </CheckoutSection>
</template>
