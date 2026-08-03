<script setup>
import { storeToRefs } from "pinia";
import { computed } from "vue";
import { useCheckoutFlowContext } from "../../modules/checkout/application/flowContext";
import { useAppDesign } from "../../design/useAppDesign";
import { buildComplementOfferRows } from "../../modules/checkout/application/preview";
import { useOrderPreview } from "../../modules/checkout/application/preview";
import { useCatalogStore } from "../../modules/catalog/store";
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
    complementLabel,
    complementProgressPercent,
    showComplementProgress,
} = useOrderPreview();

/** 2 ролла = 1 набор: показываем товары только при entitledSets >= 1. */
const hasEntitledComplementSet = computed(() => {
    const entitled = Number(complement.value?.entitledSetCount) || 0;
    if (entitled > 0) {
        return true;
    }
    return complementLines.value.some(
        (line) => (Number(line?.quantity) || 0) > 0,
    );
});

const complementRows = computed(() => {
    if (!hasCartItems.value || !hasEntitledComplementSet.value) {
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
        hasCartItems.value &&
        showComplementProgress.value &&
        !hasEntitledComplementSet.value &&
        hasRollsInCart.value,
);

const showBlock = computed(
    () => complementRows.value.length > 0 || showApproachProgress.value,
);

function paidQty(productId) {
    return cartStore.cartQuantityByProduct(productId);
}

function freeQty(row) {
    return Number(row.freeQty) || 0;
}

function displayQty(row) {
    return freeQty(row) + paidQty(row.id);
}

/** FREE — только бесплатная часть комплекта, без докупки. */
function showFreeBadge(row) {
    return freeQty(row) > 0 && paidQty(row.id) <= 0;
}

function unitPriceRub(row) {
    const product = row.product;
    if (!product) {
        return 0;
    }
    return Number(product?.price?.amount ?? product?.price) || 0;
}

/** Стоимость сверх комплекта — бейдж на месте FREE. */
function paidBadgeLabel(row) {
    const paid = paidQty(row.id);
    if (paid <= 0) {
        return "";
    }
    const total = unitPriceRub(row) * paid;
    return `+${formatPrice(total)} ₽`;
}

function canDecrement(row) {
    return paidQty(row.id) > 0;
}

function canIncrement(row) {
    return Boolean(row.product);
}

async function incrementProduct(product) {
    const id = product?.id;
    if (id == null) return;
    if (paidQty(id) <= 0) {
        await cartStore.addToCart(product, 1);
        return;
    }
    await cartStore.incrementCart(id);
}

async function decrementProduct(row) {
    if (!canDecrement(row)) return;
    await cartStore.decrementCart(row.id);
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
                            FREE
                        </span>
                        <span
                            v-else-if="paidBadgeLabel(row)"
                            :class="s.offerPaidBadge"
                        >
                            {{ paidBadgeLabel(row) }}
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
                        @click="incrementProduct(row.product)"
                    >
                        +
                    </button>
                </div>
            </li>
        </ul>
    </CheckoutSection>
</template>
