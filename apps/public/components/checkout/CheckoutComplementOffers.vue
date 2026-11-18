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
    complementLabel,
    complementProgressPercent,
    showComplementProgress,
} = useOrderPreview();

const entitledSetCount = computed(
    () => Number(complement.value?.entitledSetCount) || 0,
);

/** ceil(rollCount / 2): показываем товары при entitledSets >= 1. */
const hasEntitledComplementSet = computed(() => {
    if (entitledSetCount.value > 0) {
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
        hasCartItems.value
        && showComplementProgress.value
        && !hasEntitledComplementSet.value
        && hasRollsInCart.value,
);

const showBlock = computed(
    () => complementRows.value.length > 0 || showApproachProgress.value,
);

function paidTwinId(row) {
    const id = Number(row?.paidTwinProductId);
    return Number.isFinite(id) && id > 0 ? id : null;
}

function paidTwinProduct(row) {
    return row?.paidTwin ?? null;
}

function paidQty(row) {
    const twinId = paidTwinId(row);
    if (twinId == null) {
        return 0;
    }
    return cartStore.cartQuantityByProduct(twinId);
}

function selectedFreeQty(row) {
    return cartStore.complementSelectionQty(row.id);
}

function displayQty(row) {
    return selectedFreeQty(row) + paidQty(row);
}

/** FREE — только бесплатная часть комплекта, без докупки. */
function showFreeBadge(row) {
    return selectedFreeQty(row) > 0 && paidQty(row) <= 0;
}

function unitPriceRub(row) {
    const product = paidTwinProduct(row);
    if (!product) {
        return 0;
    }
    return Number(product?.price?.amount ?? product?.price) || 0;
}

/** Стоимость сверх комплекта — бейдж на месте FREE. */
function paidBadgeLabel(row) {
    const paid = paidQty(row);
    if (paid <= 0) {
        return "";
    }
    const total = unitPriceRub(row) * paid;
    return `+${formatPrice(total)} ₽`;
}

function canDecrement(row) {
    return selectedFreeQty(row) > 0 || paidQty(row) > 0;
}

function canIncrementFree(row) {
    return selectedFreeQty(row) < entitledSetCount.value;
}

function canIncrementPaid(row) {
    return paidTwinId(row) != null && Boolean(paidTwinProduct(row));
}

function canIncrement(row) {
    return Boolean(row.product) && (canIncrementFree(row) || canIncrementPaid(row));
}

async function incrementProduct(row) {
    const id = row.product?.id ?? row.id;
    if (id == null) {
        return;
    }

    if (canIncrementFree(row)) {
        await cartStore.setComplementSelection(id, selectedFreeQty(row) + 1);
        return;
    }

    const twinId = paidTwinId(row);
    const twinProduct = paidTwinProduct(row);
    if (twinId == null || !twinProduct) {
        return;
    }

    if (paidQty(row) <= 0) {
        await cartStore.addToCart(twinProduct, 1);
        return;
    }

    await cartStore.incrementCart(twinId);
}

async function decrementProduct(row) {
    const twinId = paidTwinId(row);

    // Сначала платные (бьют в сумму), бесплатные — только после обнуления paid.
    if (twinId != null && paidQty(row) > 0) {
        await cartStore.decrementCart(twinId);
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
