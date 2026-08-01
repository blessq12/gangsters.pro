<script setup>
import { computed } from "vue";
import { storeToRefs } from "pinia";
import { useAppDesign } from "../../design/useAppDesign";
import { buildComplementOfferRows } from "../../features/checkout/buildComplementOfferRows";
import { useOrderPreview } from "../../features/checkout/useOrderPreview";
import { useCartCommands } from "../../features/shoppingSession/useCartCommands";
import { useCatalogStore } from "../../stores/catalogStore";
import { useCheckoutStore } from "../../stores/checkoutStore";
import CheckoutSection from "./CheckoutSection.vue";

const chk = useAppDesign().components.checkout;
const c = chk.cart;
const s = chk.shared;

const catalogStore = useCatalogStore();
const checkoutStore = useCheckoutStore();
const cartCommands = useCartCommands();
const { complementProducts } = storeToRefs(catalogStore);
const { complementLines, hasRollsInCart, hasCartItems } = useOrderPreview();

const complementRows = computed(() => {
    if (!hasCartItems.value) {
        return [];
    }

    if (!hasRollsInCart.value && complementLines.value.length === 0) {
        return [];
    }

    return buildComplementOfferRows(
        complementLines.value,
        complementProducts.value,
        { includeCatalogProducts: hasRollsInCart.value },
    );
});

function paidQty(productId) {
    return checkoutStore.cartQuantityByProduct(productId);
}

function displayQty(row) {
    return (Number(row.freeQty) || 0) + paidQty(row.id);
}

function canDecrement(row) {
    return paidQty(row.id) > 0;
}

async function incrementProduct(product) {
    const id = product?.id;
    if (id == null) return;
    if (paidQty(id) <= 0) {
        await cartCommands.addProductToCart(product, 1);
        return;
    }
    await cartCommands.incrementProductInCart(id);
}

async function decrementProduct(row) {
    if (!canDecrement(row)) return;
    await cartCommands.decrementProductInCart(row.id);
}
</script>

<template>
    <CheckoutSection
        v-if="complementRows.length > 0"
        title="Комплект к роллам"
    >
        <ul :class="s.offerCardGrid">
            <li
                v-for="row in complementRows"
                :key="`complement-offer:${row.id}`"
                :class="s.offerCardCompact"
            >
                <p :class="[s.offerCardTitle, 'min-w-0 flex-1']">
                    {{ row.name }}
                </p>

                <div
                    v-if="row.product"
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
                        @click="incrementProduct(row.product)"
                    >
                        +
                    </button>
                </div>
            </li>
        </ul>
    </CheckoutSection>
</template>
