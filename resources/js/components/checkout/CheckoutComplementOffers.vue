<script setup>
import { ref } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { useComplementWizardOffers } from "../../features/checkout/useComplementWizardOffers";

const chk = useAppDesign().components.checkout;
const c = chk.cart;

const { checkoutState } = useCheckoutFlowContext();
const { formatPrice } = checkoutState;

const { offers, hasComplementOffers, addComplementToUserCart } = useComplementWizardOffers();

const addingProductId = ref(null);

async function handleAdd(productId) {
    if (addingProductId.value != null) {
        return;
    }

    addingProductId.value = productId;
    try {
        await addComplementToUserCart(productId);
    } finally {
        addingProductId.value = null;
    }
}

function offerHint(offer) {
    const parts = [];
    if (offer.freeQty > 0) {
        parts.push(`${offer.freeQty} беспл.`);
    }
    if (offer.priceRub > 0) {
        parts.push(`${formatPrice(offer.priceRub)} ₽`);
    }
    return parts.join(" · ");
}
</script>

<template>
    <ul
        v-if="hasComplementOffers"
        :class="c.systemList"
    >
        <li :class="chk.shared.subsectionKickerSm">
            Комплект
        </li>
        <li
            v-for="offer in offers"
            :key="offer.productId"
            :class="c.userLineItem"
        >
            <div class="min-w-0">
                <p :class="c.lineTitle">
                    {{ offer.name }}
                </p>
                <p :class="c.lineSub">
                    {{ offerHint(offer) }}
                </p>
            </div>

            <div :class="c.lineActions">
                <div :class="c.qtyBar">
                    <span :class="c.qtyLabel">
                        {{ offer.userQty }}
                    </span>
                    <button
                        type="button"
                        :class="c.qtyBtn"
                        :disabled="addingProductId === offer.productId"
                        @click="handleAdd(offer.productId)"
                    >
                        +
                    </button>
                </div>
            </div>
        </li>
    </ul>
</template>
